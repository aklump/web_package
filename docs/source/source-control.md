# What to Commit to Source Control?

* The project installs a sane _.gitignore_ file in _.web_package_ on `init`, which should take care of most cases.
* If you want to use some of the functionality in the _.web_package/functions.php_ or _.web_package/functions.sh_ files, you will need to remove the `*` from _.web_package/.gitignore_ as necessary for the scope of usage.
