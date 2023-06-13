#!/bin/sh
npm run build
rsync -av ./ u112485420@access964972401.webspace-data.io:~/marrymix --include=public/build --include=node_modules --exclude-from=.gitignore --exclude=".*" 