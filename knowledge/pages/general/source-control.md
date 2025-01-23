<!--
id: source_control
tags: ''
-->

# Should `.web_package` be added to source control?

1. Yes.
2. Make sure the file _.web\_package/.gitignore_ exists.
3. It should at least have the following:

    ```gitignore
    {{ web_package_gitignore }}
    ```
