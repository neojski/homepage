#!/bin/bash
# Deploy the site.
#
# /var/www/html on the server is a git checkout of this repository, so
# deploying is a push followed by a pull - nothing is copied and nothing is
# deleted. That matters: the server also carries .well-known/ (Let's Encrypt
# renewal), profiler/ and remember/, none of which are in git and none of
# which exist anywhere else. Never run `git clean` on that checkout.
set -e -u -o pipefail

host=digitalocean          # ssh alias; see ~/.ssh/config
root=/var/www/html
branch=master

cd "$(dirname "$0")"

here="$(git rev-parse --abbrev-ref HEAD)"
if [ "$here" != "$branch" ]; then
  echo "On '$here', not '$branch'. Switch before deploying." >&2
  exit 1
fi

if [ -n "$(git status --porcelain)" ]; then
  echo 'Uncommitted changes - the deploy would not include them:' >&2
  git status --short >&2
  exit 1
fi

git fetch -q origin
local_head="$(git rev-parse HEAD)"
live="$(ssh "$host" "git -C '$root' rev-parse HEAD")"

if [ "$live" = "$local_head" ]; then
  echo "Already up to date: $(git log --oneline -1 HEAD)"
  exit 0
fi

echo "server  $(git log --oneline -1 "$live" 2>/dev/null || echo "$live (unknown here)")"
echo "local   $(git log --oneline -1 HEAD)"
echo
echo 'Going live:'
if git merge-base --is-ancestor "$live" HEAD 2>/dev/null; then
  git log --oneline "$live..HEAD" | sed 's/^/  /'
else
  echo "  server is not an ancestor of local - it has diverged." >&2
  echo "  Sort that out by hand; this script will not force anything." >&2
  exit 1
fi
echo

read -r -p 'Continue? ' _

git push origin "$branch"
ssh "$host" "cd '$root' && git pull --ff-only"

echo
echo "now live: $(ssh "$host" "git -C '$root' log --oneline -1")"
