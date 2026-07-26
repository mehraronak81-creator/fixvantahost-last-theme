start=`date +%s`

# Build the panel
yarn build:production

# Mkdir and cd to tmp
mkdir tmp
cd tmp/

# Clone the current VantaHost checkout so releases never depend on an
# unrelated upstream repository or a hard-coded GitHub account.
git clone .. VantaHost

cd VantaHost

# Remove the .git folder
rm -rf .git

# Copy the Compiled files
cp -r ../../public .

# Create the tar and zip files
tar -czvf ./VantaHost.tar.gz .
zip -r ./VantaHost.zip .

rm -rf ../../release/*

# Create releases folder if it doesn't exist
mkdir ../../release

# Move the files to releases
mv ./VantaHost.tar.gz ../../release/panel.tar.gz
mv ./VantaHost.zip ../../release/panel.zip

# Remove the tmp folder
cd ../../
rm -rf tmp/

end=`date +%s`

# Done
echo "Build in `expr $end - $start` seconds"
