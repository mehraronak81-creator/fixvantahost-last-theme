<?php

namespace Pterodactyl\Services\StatusWidget;

/**
 * Pure-PHP implementation of the modern Minecraft Server List Ping (SLP)
 * protocol plus a generic TCP latency probe used as a fallback for
 * non-Minecraft game servers.
 *
 * No external packages are used. Every probe fails closed to null: nothing
 * here ever throws an uncaught exception or hangs past the supplied timeout.
 */
class MinecraftPingService
{
    /**
     * Protocol version sent in the handshake. Using -1 signals a status-only
     * "I have no idea what version I am" handshake which every modern server
     * answers; 47 (1.8) is an equally safe alternative. We keep it explicit.
     */
    private const PROTOCOL_VERSION = -1;

    /**
     * Hard ceiling on the bytes we are willing to read for the JSON status
     * payload. Guards against a hostile/garbage server trying to make us read
     * forever. 256 KiB is far larger than any legitimate status response.
     */
    private const MAX_RESPONSE_LENGTH = 262144;

    /**
     * Perform a full modern Minecraft Server List Ping against the given host.
     *
     * @return array{
     *     online: bool,
     *     player_count: int,
     *     max_players: int,
     *     version: string,
     *     motd: string,
     *     ping_ms: int
     * }|null  Null on any failure whatsoever.
     */
    public function query(string $ip, int $port, int $timeout = 2): ?array
    {
        // Suppress connection warnings; we translate every failure into null.
        $errno = 0;
        $errstr = '';

        $start = microtime(true);

        $socket = @fsockopen($ip, $port, $errno, $errstr, $timeout);
        if ($socket === false) {
            return null;
        }

        try {
            stream_set_timeout($socket, $timeout);
            // Read in blocking mode so our length-prefixed reads behave.
            stream_set_blocking($socket, true);

            // ---- Handshake packet (next state = 1 / status) ----
            $handshake = $this->writeVarInt(0x00)                    // packet id
                . $this->writeVarInt(self::PROTOCOL_VERSION)         // protocol version
                . $this->writeString($ip)                            // server address
                . pack('n', $port)                                   // server port (unsigned short, big-endian)
                . $this->writeVarInt(1);                             // next state: status

            $this->writePacket($socket, $handshake);

            // ---- Status request packet (empty body, id 0x00) ----
            $this->writePacket($socket, $this->writeVarInt(0x00));

            // ---- Read the status response ----
            // Outer packet length (VarInt) then packet id (VarInt) then the
            // length-prefixed JSON string.
            $packetLength = $this->readVarInt($socket);
            if ($packetLength === null || $packetLength <= 0 || $packetLength > self::MAX_RESPONSE_LENGTH) {
                return null;
            }

            $packetId = $this->readVarInt($socket);
            if ($packetId === null || $packetId !== 0x00) {
                return null;
            }

            $jsonLength = $this->readVarInt($socket);
            if ($jsonLength === null || $jsonLength <= 0 || $jsonLength > self::MAX_RESPONSE_LENGTH) {
                return null;
            }

            $json = $this->readBytes($socket, $jsonLength);
            if ($json === null) {
                return null;
            }

            $ping = (int) round((microtime(true) - $start) * 1000);
        } catch (\Throwable $e) {
            return null;
        } finally {
            // Always release the socket.
            if (is_resource($socket)) {
                @fclose($socket);
            }
        }

        $data = json_decode($json, true);
        if (!is_array($data)) {
            return null;
        }

        $players = $data['players'] ?? [];
        $playerCount = isset($players['online']) && is_numeric($players['online']) ? (int) $players['online'] : 0;
        $maxPlayers = isset($players['max']) && is_numeric($players['max']) ? (int) $players['max'] : 0;

        $version = '';
        if (isset($data['version']['name']) && is_string($data['version']['name'])) {
            $version = $this->stripFormatting($data['version']['name']);
        }

        $motd = '';
        if (array_key_exists('description', $data)) {
            $motd = $this->stripFormatting($this->flattenChatComponent($data['description']));
        }

        return [
            'online' => true,
            'player_count' => $playerCount,
            'max_players' => $maxPlayers,
            'version' => $version,
            'motd' => $motd,
            'ping_ms' => max(0, $ping),
        ];
    }

    /**
     * Generic TCP latency probe. Opens a socket to the host/port and, if it
     * connects, returns the round-trip latency in milliseconds. Returns null
     * if the connection cannot be established. Used as a fallback for game
     * servers that do not speak the Minecraft SLP protocol.
     */
    public function tcpProbe(string $ip, int $port, int $timeout = 2): ?int
    {
        $errno = 0;
        $errstr = '';

        $start = microtime(true);
        $socket = @fsockopen($ip, $port, $errno, $errstr, $timeout);

        if ($socket === false) {
            return null;
        }

        $latency = (int) round((microtime(true) - $start) * 1000);

        if (is_resource($socket)) {
            @fclose($socket);
        }

        return max(0, $latency);
    }

    /**
     * Write a length-prefixed packet to the socket. The Minecraft protocol
     * frames each packet as: VarInt(length of payload) + payload.
     *
     * @param resource $socket
     */
    private function writePacket($socket, string $payload): void
    {
        $framed = $this->writeVarInt(strlen($payload)) . $payload;
        $written = @fwrite($socket, $framed);

        if ($written === false || $written < strlen($framed)) {
            throw new \RuntimeException('Failed to write packet to socket.');
        }
    }

    /**
     * Encode an integer as a Minecraft protocol VarInt (LEB128, 7 bits per
     * byte, high bit as continuation flag). Handles negative values by
     * treating them as their 32-bit two's-complement representation.
     */
    private function writeVarInt(int $value): string
    {
        // Constrain to 32 bits (unsigned interpretation) to match the wire
        // format. Negative numbers like the protocol version -1 become a full
        // 5-byte VarInt, which is exactly what vanilla servers expect.
        $value &= 0xFFFFFFFF;
        $out = '';

        do {
            $temp = $value & 0x7F;
            $value = ($value >> 7) & 0x01FFFFFF; // logical right shift, 32-bit safe
            if ($value !== 0) {
                $temp |= 0x80;
            }
            $out .= chr($temp);
        } while ($value !== 0);

        return $out;
    }

    /**
     * Encode a UTF-8 string as a Minecraft protocol string: VarInt length
     * prefix (in bytes) followed by the raw UTF-8 bytes.
     */
    private function writeString(string $value): string
    {
        return $this->writeVarInt(strlen($value)) . $value;
    }

    /**
     * Read a VarInt from the socket one byte at a time. Returns null on EOF,
     * timeout, or if the VarInt is malformed (more than 5 bytes).
     *
     * @param resource $socket
     */
    private function readVarInt($socket): ?int
    {
        $result = 0;
        $position = 0;

        while (true) {
            $byte = $this->readBytes($socket, 1);
            if ($byte === null) {
                return null;
            }

            $value = ord($byte);
            $result |= ($value & 0x7F) << (7 * $position);

            if (($value & 0x80) === 0) {
                break;
            }

            $position++;
            if ($position >= 5) {
                // VarInts are at most 5 bytes; anything longer is corrupt.
                return null;
            }
        }

        return $result;
    }

    /**
     * Read exactly $length bytes from the socket, looping until satisfied or
     * the stream ends/times out. Returns null if the full length cannot be
     * read.
     *
     * @param resource $socket
     */
    private function readBytes($socket, int $length): ?string
    {
        if ($length === 0) {
            return '';
        }

        $buffer = '';
        $remaining = $length;

        while ($remaining > 0) {
            $chunk = @fread($socket, $remaining);

            if ($chunk === false || $chunk === '') {
                // Detect timeout / closed stream and bail out.
                $meta = @stream_get_meta_data($socket);
                if (!empty($meta['timed_out']) || !empty($meta['eof'])) {
                    return null;
                }

                return null;
            }

            $buffer .= $chunk;
            $remaining -= strlen($chunk);
        }

        return $buffer;
    }

    /**
     * Flatten a Minecraft "chat component" into plain text. The MOTD's
     * description field may be:
     *   - a plain string,
     *   - a chat object {"text": "...", "extra": [...]},
     *   - or a nested array of such objects.
     * We recurse, concatenating every "text" value and every "extra" child in
     * order, producing a single readable line.
     */
    private function flattenChatComponent(mixed $component): string
    {
        if (is_string($component)) {
            return $component;
        }

        if (is_array($component)) {
            // A bare list of components (no associative keys).
            if (array_is_list($component)) {
                $text = '';
                foreach ($component as $child) {
                    $text .= $this->flattenChatComponent($child);
                }

                return $text;
            }

            $text = '';

            if (isset($component['text']) && is_string($component['text'])) {
                $text .= $component['text'];
            }

            if (isset($component['extra']) && is_array($component['extra'])) {
                foreach ($component['extra'] as $child) {
                    $text .= $this->flattenChatComponent($child);
                }
            }

            return $text;
        }

        return '';
    }

    /**
     * Strip legacy section-sign (§) colour/format codes and collapse the
     * surrounding whitespace into something presentable in a small widget.
     */
    private function stripFormatting(string $text): string
    {
        // Remove §-prefixed codes (the section sign is two bytes in UTF-8).
        $clean = preg_replace('/\x{00A7}./u', '', $text);
        if ($clean === null) {
            // preg failure (e.g. invalid UTF-8) -> fall back to the raw text.
            $clean = $text;
        }

        // Normalise newlines/tabs into spaces and collapse runs of whitespace.
        $clean = preg_replace('/\s+/u', ' ', $clean) ?? $clean;

        return trim($clean);
    }
}

