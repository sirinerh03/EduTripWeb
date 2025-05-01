#!/bin/bash

# Remplacer les secrets dans le fichier
git filter-branch --force --index-filter \
  "git ls-files -z 'src/Controller/GoogleLoginController.php' | \
   xargs -0 sed -i 's/251927211336-sdh8ptc5mqfstd9eto41mnd6ba3q9hbp.apps.googleusercontent.com/\$_ENV[\"GOOGLE_ID\"]/g; \
   s/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/\$_ENV[\"GOOGLE_SECRET\"]/g'" \
  --prune-empty --tag-name-filter cat -- --all
