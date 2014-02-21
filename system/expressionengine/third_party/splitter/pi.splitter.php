<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
========================================================
 
--------------------------------------------------------
 Copyright: Oliver Heine
 http://utilitees.de/ee.php/splitter
--------------------------------------------------------
 This addon may be used free of charge. Should you
 employ it in a commercial project of a customer or your
 own I'd appreciate a small donation.
========================================================
 File: pi.splitter.php
--------------------------------------------------------
 Purpose: Split any kind of lists into blocks.
========================================================
 THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF
 ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT 
 LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
 FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO 
 EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE
 FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN
 AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE 
 OR OTHER DEALINGS IN THE SOFTWARE.
=========================================================

*/


$plugin_info = array(
                        'pi_name'			=> 'Splitter',
                        'pi_version'		=> '2.0',
                        'pi_author'		=> 'Oliver Heine',
                        'pi_author_url'		=> 'http://utilitees.de/ee.php/splitter',
                        'pi_description'	=> 'Split any kind of lists into blocks.',
                        'pi_usage'		=> Splitter::usage()
                    );


class Splitter {

    var $return_data;
        
    function Splitter ()
    {
  
        $this->EE =& get_instance();
     	
        $tagdata = $this->EE->TMPL->tagdata;

        $delimiter = $this->EE->TMPL->fetch_param('delimiter');
        $blocks = $this->EE->TMPL->fetch_param('blocks');
        $class = $this->EE->TMPL->fetch_param('class');
        $block_start = $this->EE->TMPL->fetch_param('block_start','');
        $block_end = $this->EE->TMPL->fetch_param('block_end','');

        if (!defined('PAGINATION')) define('PAGINATION', '<!-- pagination //-->') ;        
        // find pagination
        $pc = substr_count($tagdata, PAGINATION);
	    
        if ( $pc == 2 || $pc == 4 )
	    {
            $paginationp = "bottom";
            $begin = strpos($tagdata,PAGINATION);
            $end =   strpos($tagdata,PAGINATION, $begin + strlen(PAGINATION));
            $pagination = substr($tagdata,$begin+strlen(PAGINATION),$end-$begin-strlen(PAGINATION));
            $tagdata = substr($tagdata, 0, $begin) . substr($tagdata,$end+strlen(PAGINATION));
            if ( $begin < 20 )
            {
                $paginationp = "top";
            }
	    }

		if ( $pc == 4 )
		{
	            $begin = strpos($tagdata,PAGINATION);
	            $end =   strpos($tagdata,PAGINATION, $begin + strlen(PAGINATION));
	            $tagdata = substr($tagdata, 0, $begin) . substr($tagdata, $end+strlen(PAGINATION));
		}      
      
        if ($blocks === FALSE OR $delimiter === FALSE)
        {
          	$this->return_data = $tagdata;
          	return;
        }

		$cat_array = explode($delimiter, $tagdata);
        foreach ($cat_array AS $key => $value )
        {
            $value = trim($value);
            if ( empty($value) )
            {
                unset($cat_array[$key]);
            }
        }
		$num_cats = count($cat_array);

        if ( $num_cats == 0 )
        {
            $this->return_data = $tagdata;
            return;
         }
      		  
	 	$limit = ceil($num_cats / $blocks);
	
        $mykeys = array_keys($cat_array);
	 	$cc = 0; 
        $output = "\n<div class=\"$class\">";
        if ( !empty($block_start) )
        {
           $output .= $block_start;
        }

        $ll = 0;
        foreach ($mykeys AS $keyname)
        {
           if ($cc == $limit)
           {
                if ( !empty($block_end) )
                {
                    $output .= $block_end;
                }
                $output .= "\n</div>\n\n<div class=\"$class\">";

                if ( !empty($block_start) )
                {
                    $output .= $block_start;
                }
                $cc = 0;
        	}

        	$cc++;
	    	$output .= $cat_array[$keyname].$delimiter;
        }

        if ( !empty($block_end) )
        {
            $output .= $block_end;
        }
        
        $output .= "\n</div>\n";
      
      	// re-add pagination
      	if ( $pc == 2 ) 
      	{
      		if ($paginationp == "top")
      		{
	      		$output = $pagination . $output;
    	  	}
      		else
      		{	
      			$output .= $pagination;
      		}
      	}
      	if ( $pc == 4 ) 
      	{
			$output = $pagination . $output . $pagination;
		}      
       
      	$this->return_data = $output;
    }
    /* END */
  

    
// ----------------------------------------
//  Plugin Usage
// ----------------------------------------

// This function describes how the plugin is used.
//  Make sure and use output buffering

function usage()
{
ob_start(); 
?>
This plugin may be used to split any kind of listings into several blocks with evenly distributed items. This can be manually created lists, lists produced by {exp:channel:entries}- or {exp:channel:categories}-tags or whatever.
There must however be some text in between the single items that can be used as a delimiter.

The number of items per block is determined automatically and blocks surrounded by <div></div> are created. You may specify a classname to be used in the <div>.

~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
EXAMPLE:
========
{exp:channel:entries channel="default"}
{title}<br />
{/exp:channel:entries}

You have a list of channel entries and want to create three evenly sized columns.
Wrap the {exp:splitter}-tags around the {exp:channel:entries}-tags, specifiy the number of blocks you want, a text to be used as delimiter and the class you want to be applied to the <div>s.

{exp:splitter blocks="3" delimiter="<br />" class="myclass"}
	{exp:channel:entries channel="default"}
	{title}<br />
	{/exp:channel:entries}
{/exp:splitter}

will produce

<div class="myclass">
Title 1<br />
Title 2<br />
Title 3<br />
</div>
<div class="myclass">
Title 4<br />
Title 5<br />
Title 6<br />
</div>
<div class="myclass">
Title 7<br />
Title 8<br />
Title 9<br />
</div>

To make columns you'd probably set the class to float:left and might want to specify a width. Like:

<style>
.myclass {
 float:left;
 width:33%;
}
</style>
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~


USAGE DETAILS:
==============

TAG:
----
{exp:splitter}{/exp:splitter}

PARAMETERS:
-----------

blocks="X"
Mandatory. Sets the number of blocks you want.

block_start=""
Optional. Text to be added after each opening <div>.

block_end=""
Optional. Text to be added before each closing </div>.

class="X"
Optional. Sets the CSS class name for the <div> surrounding a block of list items.

delimiter="X"
Mandatory. This must be a unique string in your list that separates any two items.

--- Example 1 ---
{exp:channel:categories channel="default" style="linear"}
<li><a href="{path=site/index}">{category_name}</a></li>
{/exp:channel:categories}

In this example the delimiter would be: </li>
--------------------

--- Example 2 ---
{exp:channel:entries channel="default"}
<a href="{permalink=site/comments}">{title}</a><br />
{/exp:channel:entries}

In this example the delimiter would be: <br />
--------------------

--- Example 3 ---
1<!-- //-->2<!-- //-->3<!-- //-->4<!-- //-->5<!-- //-->

In this example the delimiter would be: <!-- //-->
--------------------

I hope you get the point ;-)


NOTE ON PAGINATION:
-------------------
If you use Splitter in combination with {exp:channel:entries} or 
{exp:comment:entries} and want to use pagination you have to place special markers 
inside your {paginate}-tagpair to help Splitter identify the pagination 
codeblock.

Just insert <!-- pagination //--> after the opening and before the closing
{paginate}-tag. The markers will be completely removed before the page is 
rendered.

Example 1:
----------
{paginate}<!-- pagination //-->
<p>Page {current_page} of {total_pages} pages {pagination_links}</p>
<!-- pagination //-->{/paginate}

Example 2:
----------
{paginate}<!-- pagination //-->
{if previous_page}<a href="{auto_path}">Previous Page</a> &nbsp;{/if}
{if next_page}<a href="{auto_path}">Next Page</a>{/if}
<!-- pagination //-->{/paginate}



CHANGELOG:
==========

2.0
Inital release for Expression Engine 2


<?php
$buffer = ob_get_contents();
	
ob_end_clean(); 

return $buffer;
}
/* END */


}
/* END Class */
/* End of file pi.splitter.php */
/* Location: ./system/expressionengine/third_party/splitter/pi.splitter.php */