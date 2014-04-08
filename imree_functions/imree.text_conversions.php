<?php

/**
 * @file This file handles text manipulation
 */

/**
 * Takes html and returns text flow.
 * @param string $original_string
 * @param string $flow_attributes
 * @author Jessica Harvey <jsharvey55@gmail.com>
 * @return string
 */

function html_to_textflow($original_string, $flow_attributes) {
    $supportedTags = '<b> <br> <br/> <h1> <h2> <h3> <h4> <h5> <h6> <i> <u>';
    /* list of html tags to be converted to textflow.
     * When adding a new tag to this list, you will also need to add a 
     * pattern & replacement for it to $flowtext
     */
    $rcs = preg_replace('/(<(head|script)[^>]*?>.*?<\/\2>)/i', '', $original_string);
    //script and style content causes problems if present
    $rTags = strip_tags($rcs, $supportedTags);
    //removes all unsupported html tags
    $htmlbracket = preg_replace_callback(//this *should* wrap all tags included in $supportedTags other than the font style tags in encoded angle brackets. This is necessary for comparison of font style tags later.
            '/(<)(\/)?([^>]*?)(>)/i', function($match) {
                if (in_array($match[3], array(1 => "i", 2 => "b", 3 => "u"))) {
                    return $match[0];
                } else {
                    return "&lt;$match[2]$match[3]&gt;";
                }
            }, $rTags);

    $tagcall = function($match) {
                $m1 = preg_replace('/^<([biu])>(<([biu])>)?(<([biu])>)?(.*?)<\/([biu])>(<\/([biu])>)?(<\/([biu])>)?$/i', '<flow:span $1$3$5>$6</flow:span>', $match[0]);
                $inner = function($ma) {
                            $p = preg_replace('/(?<!^)<([biu])>/', "<$ma[1]$1>", $ma[0]);
                            return $p;
                        };
                $m2 = preg_replace_callback('/(?<!^)<([biu])>(.*?)<\/\1>/', $inner, $m1);
                //matches the middle font (if all 3 are present) runs $inner to add the middle font to the innermost tags
                $m3 = preg_replace(array(
                    1 => '/(?<!^)<([biu])([biu])?>/i', // adds outermost font style to middle and innermost tags, converts to flow spans:
                    //<flow:span i>sometext<b>moretext<bu>more</u>text</flow:span> => 
                    //<flow:span i>sometext<flow:span ib>moretext<flow:span ibu>more</u>text</flow:span>
                    2 => '/<\/([biu])><\/([biu])>/i', //combines adjacent closing font tags:
                    //<flow:span i>sometext<flow:span ibu>moretext</b></u> more</flow:span> => 
                    //<flow:span i>sometext<flow:span ibu>moretext</bu>more</flow:span>
                    3 => '/<(?:flow:span )?([biu])([biu]?)([biu])?>([^>]*?)(?=<(?:flow:span )?([biu])([biu])?([biu])?>)/i', //looks for two opening flow spans without a closing tag between them, inserts a closing span.
                    //<flow:span i>sometext<flow:span ib>moretext<flow:span ibu>
                    //<flow:span i>sometext</flow:span><flow:span ib>moretext</flow:span><flow:span ibu>
                    4 => '/<flow:span ([biu])([biu])?([biu])>([^>]*?)<\/\3>/', //looks for a open 2-3 font tag followed by a closing tag for the final font; closes the span and opens one for the remaining font(s) 
                    5 => '/<flow:span ([biu])([biu])?([biu])>([^>]*?)<\/\3(\2)?>/'//looks for an open 2-3 font tag followed by a closing tag for the final 1-2 fonts; closes the span and opens another for the remaining font(s)
                        ), array(
                    1 => "<flow:span $match[1]$1$2>",
                    2 => "</$1$2>",
                    3 => '<flow:span $1$2$3>$4</flow:span>',
                    4 => '<flow:span $1$2$3>$4</flow:span><flow:span $1$2>',
                    5 => '<flow:span $1$2$3>$4</flow:span><flow:span $1>'
                        ), $m2);
                $flowfont = preg_replace_callback('/<flow:span[^>]*?([biu])([biu])?([biu])?([^>]*?)>/i', function($match) { //changes b i and u to tlf attributes
                            $repl = preg_replace(array('/i/i', '/b/i', '/u/i'), array(' fontStyle="italic"', ' fontWeight="bold"', ' textDecoration="underline"'), $match[0]);
                            return $repl;
                        }, $m3);
                return $flowfont;
            };
    $tagchunk = preg_replace_callback('/<([biu])>.*?<\/\1>/i', $tagcall, $htmlbracket);
    //takes chunks of html defined by outside set of matching opening + closing font style tags, runs $tagcall on them

    $flowtext = preg_replace(
            array(
        1 => '/&lt;(h[1-6])[^>]*?&gt;([^>]*?)&lt;\/\1&gt;/i',
        // 2=>'/&lt;a href="(.*?)"&gt;(.*?)&lt;\/a&gt;/i',
        3 => '/<\/[iub]>/i', //converts any stray closing font style tags to closing flows
        4 => '/(?|(<\/flow:[^>]*?>)|(^))([^>]+?)((<flow)|$)/i', //adds normal flow spans between other style format spans (and links, if supported)
        5 => '/&lt;br&gt;/i',
        6 => '/^(.*?)$/',), array(
        1 => '<flow:span styleName="$1">$2</flow:span>',
        //  2=>'<flow:a href="$1" target="_self"><flow:span>$2</flow:span></flow:a>',
        3 => '</flow:span>',
        4 => '$1<flow:span>$2</flow:span>$3',
        5 => '<flow:br/>',
        6 => "<flow:TextFlow xmlns:flow='http://ns.adobe.com/textLayout/2008' $flow_attributes><flow:p>$1</flow:p></flow:TextFlow>"), $tagchunk);
    return $flowtext;
}