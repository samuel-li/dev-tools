# dev-tools
## Fix cache issue when build code can not start
cp repair.php SUGARCRM_ROOT/ ; php repair.php

## Build docker for running SugarCRM code
refer : https://hub.docker.com/r/tuxia1980/sugar-php


## Tool : Generate Hooks Reference
Usage:
1. download `generate_hooks_index.php` and put it in the root director of SugarCRM build
2. run "php generate_hooks_index.php"
3. The hook's index will be generated in "HooksRef/" directory.
4. Click "index.html" to review all hooks.
