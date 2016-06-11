#!/bin/bash
# 
# @file
# 
# This file will get called if lobster_debug == 1 at the time the function
# lobster_show_debug() is called.

lobster_color 'yellow'
lobster_echo "DEBUG INFO" "This will only be called if you set lobster_debug=1 in the config file"

lobster_color 'green'
lobster_echo "\$lobster_app_title:" "$lobster_app_title" " "
lobster_echo "\$lobster_app_name:" "$lobster_app_name" " "
lobster_echo "\$LOBSTER_PWD:" "$LOBSTER_PWD" " "
lobster_echo "\$LOBSTER_PWD_ROOT:" "$LOBSTER_PWD_ROOT" " "
lobster_echo "\$LOBSTER_APP:" "$LOBSTER_APP" " "
lobster_echo "\$LOBSTER_APP_ROOT:" "$LOBSTER_APP_ROOT" " "
lobster_echo "\$LOBSTER_ROOT:" "$LOBSTER_ROOT" " "
lobster_echo "\$lobster_app_config:" "$lobster_app_config" " "
lobster_echo "\$lobster_php:" "$lobster_php" " "
lobster_echo "\$lobster_op:" "$lobster_op" " "
lobster_echo "\$lobster_route:" "$lobster_route" " "
lobster_echo "\$lobster_args"
lobster_echo '    [#] => '${#lobster_args[@]}
lobster_echo '    [0] => '${lobster_args[0]}
lobster_echo '    [1] => '${lobster_args[1]}
lobster_echo '    [2] => '${lobster_args[2]}
lobster_echo '    [3] => '${lobster_args[3]}
lobster_echo '    [4] => '${lobster_args[4]}
lobster_echo "\$lobster_params"
lobster_echo '    [#] => '${#lobster_params[@]}
lobster_echo '    [0] => '${lobster_params[0]}
lobster_echo '    [1] => '${lobster_params[1]}
lobster_echo '    [2] => '${lobster_params[2]}
lobster_echo '    [3] => '${lobster_params[3]}
lobster_echo '    [4] => '${lobster_params[4]}
lobster_echo "\$lobster_flags"
lobster_echo '    [#] => '${#lobster_flags[@]}
lobster_echo '    [0] => '${lobster_flags[0]}
lobster_echo '    [1] => '${lobster_flags[1]}
lobster_echo '    [2] => '${lobster_flags[2]}
lobster_echo '    [3] => '${lobster_flags[3]}
lobster_echo '    [4] => '${lobster_flags[4]}
lobster_echo "\$lobster_debug:" $lobster_debug " "
lobster_echo "\$lobster_logs:" $lobster_logs " "
lobster_echo "\$LOBSTER_JSON:" "$LOBSTER_JSON" " "
