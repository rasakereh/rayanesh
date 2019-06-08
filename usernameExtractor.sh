while read p; do
  ls "../../$p" >> public_html/usernames.txt
  echo "ls ../../$p"
done <usernameDirs.txt
