# Changelog

## 0.5.8
* Deprecated class `String`, replaced with `Strings`; please change all instances.

## 0.5.5
* It is now possible to provide a custom extension, such as `.info` instead of `.ini`.

## 0.5
* ConfigFileBasedStorage will now automatically add the extension to the basename if it is not present.  This can be disabled with the option 'auto_extension' = false
* The ConfigYaml extension is now .yaml not .yml per <http://www.yaml.org/faq.html>; if this breaks your app, then extend the class with a new constant.
