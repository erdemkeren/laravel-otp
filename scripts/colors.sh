#!/bin/bash
# SDT
COLOR_ESC="\033["
COLOR_RESET=0
COLOR_RESET_UNDERLINE=24
COLOR_RESET_REVERSE=27
COLOR_DEFAULT=39
COLOR_DEFAULTB=49
COLOR_BOLD=1
COLOR_BRIGHT=2
COLOR_UNDERSCORE=4
COLOR_REVERSE=7
COLOR_BLACK=30
COLOR_RED=31
COLOR_GREEN=32
COLOR_BROWN=33
COLOR_BLUE=34
COLOR_MAGENTA=35
COLOR_CYAN=36
COLOR_WHITE=37
COLOR_BLACKB=40
COLOR_REDB=41
COLOR_GREENB=42
COLOR_BROWNB=43
COLOR_BLUEB=44
COLOR_MAGENTAB=45
COLOR_CYANB=46
COLOR_WHITEB=47

function color_escape
{
    local result="$1"
    until [ -z "$2" ]; do
	if ! [ $2 -ge 0 -a $2 -le 47 ] 2>/dev/null; then
	    echo "color_escape: argument \"$2\" is out of range" >&2 && return 1
	fi
        result="${COLOR_ESC}${2}m${result}${COLOR_ESC}${COLOR_RESET}m"
	shift || break
    done

    echo -e "$result"
}

function color_reset           { color_escape "$1" $COLOR_RESET;           }
function color_reset_underline { color_escape "$1" $COLOR_RESET_UNDERLINE; }
function color_reset_reverse   { color_escape "$1" $COLOR_RESET_REVERSE;   }
function color_default         { color_escape "$1" $COLOR_DEFAULT;         }
function color_defaultb        { color_escape "$1" $COLOR_DEFAULTB;        }
function color_bold            { color_escape "$1" $COLOR_BOLD;            }
function color_bright          { color_escape "$1" $COLOR_BRIGHT;          }
function color_underscore      { color_escape "$1" $COLOR_UNDERSCORE;      }
function color_reverse         { color_escape "$1" $COLOR_REVERSE;         }
function color_black           { color_escape "$1" $COLOR_BLANK;           }
function color_red             { color_escape "$1" $COLOR_RED;             }
function color_green           { color_escape "$1" $COLOR_GREEN;           }
function color_brown           { color_escape "$1" $COLOR_BROWN;           }
function color_blue            { color_escape "$1" $COLOR_BLUE;            }
function color_magenta         { color_escape "$1" $COLOR_MAGENTA;         }
function color_cyan            { color_escape "$1" $COLOR_CYAN;            }
function color_white           { color_escape "$1" $COLOR_WHITE;           }
function color_blackb          { color_escape "$1" $COLOR_BLACKB;          }
function color_redb            { color_escape "$1" $COLOR_REDB;            }
function color_greenb          { color_escape "$1" $COLOR_GREENB;          }
function color_brownb          { color_escape "$1" $COLOR_BROWNB;          }
function color_blueb           { color_escape "$1" $COLOR_BLUEB;           }
function color_magentab        { color_escape "$1" $COLOR_MAGENTAB;        }
function color_cyanb           { color_escape "$1" $COLOR_CYANB;           }
function color_whiteb          { color_escape "$1" $COLOR_WHITEB;          }
function say_title           { color_blueb  "$1"; }
function say_okay            { color_cyanb  "$1"; }
function say_error           { color_redb  "$1"; }
function say_message         { color_bold  "$1"; }
function say_command         { color_bold  "cmd - $1"; }


function color_dump
{
    local T='gYw'

    echo -e "\n                 40m     41m     42m     43m     44m     45m     46m     47m";

    for FGs in '   0m' '   1m' '  30m' '1;30m' '  31m' '1;31m' \
               '  32m' '1;32m' '  33m' '1;33m' '  34m' '1;34m' \
               '  35m' '1;35m' '  36m' '1;36m' '  37m' '1;37m';
    do
        FG=${FGs// /}
        echo -en " $FGs \033[$FG  $T  "
        for BG in 40m 41m 42m 43m 44m 45m 46m 47m; do
            echo -en " \033[$FG\033[$BG  $T  \033[0m";
        done
        echo;
    done

    echo
    color_bold "    Code     Function           Variable"
    echo \
'    0        color_reset          $COLOR_RESET
    1        color_bold           $COLOR_BOLD
    2        color_bright         $COLOR_BRIGHT
    4        color_underscore     $COLOR_UNDERSCORE
    7        color_reverse        $COLOR_REVERSE

    30       color_black          $COLOR_BLACK
    31       color_red            $COLOR_RED
    32       color_green          $COLOR_GREEN
    33       color_brown          $COLOR_BROWN
    34       color_blue           $COLOR_BLUE
    35       color_magenta        $COLOR_MAGENTA
    36       color_cyan           $COLOR_CYAN
    37       color_white          $COLOR_WHITE

    40       color_blackb         $COLOR_BLACKB
    41       color_redb           $COLOR_REDB
    42       color_greenb         $COLOR_GREENB
    43       color_brownb         $COLOR_BROWNB
    44       color_blueb          $COLOR_BLUEB
    45       color_magentab       $COLOR_MAGENTAB
    46       color_cyanb          $COLOR_CYANB
    47       color_whiteb         $COLOR_WHITEB
'
}
