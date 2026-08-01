#coding-demo

git push origin master
//git push -f -u origin master
git push -u origin
git push -u master

$url="https://"
git remote set-url --add master $url 
git remote set-url --delete master $url
git remote -v
