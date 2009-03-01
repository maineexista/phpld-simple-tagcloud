<?php

/**
#########################################################################################################
# Project:     PHP Link Directory - Link exchange directory @ http://www.phplinkdirectory.com/
# Module:      phpLD Simple TagCloud
# Homepage:    http://www.frozenminds.com/phpld-tagclouds.html
# Author:      Constantin Bejenaru aKa Boby @ http://www.frozenminds.com/
# Language:    PHP + xHTML + CSS
# License:     GPL @ http://www.gnu.org/copyleft/gpl.html
# Version:     1.3
# Notice:      Please maintain this section
#########################################################################################################
# Copyright (c) 2006-2009 Constantin Bejenaru - http://www.frozenminds.com/
#
# This program is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License
# as published by the Free Software Foundation; either version 2
# of the License, or (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
#########################################################################################################
 */

class phpld_tagclouds
{

   /**
    * Array with words and word count
    *
    * @var array
    */
   protected $_tags = array ();

   /**
    * String of words
    *
    * @var string
    */
   protected $_tags_string = '';

   /**
    * Maximum allowed words (most important selected, the rest is removed)
    *
    * @var integer (zero or NULL means infinite)
    */
   protected $_maxWordsCount = 20;

   /**
    * Minimum occurrences of a word to be displayed
    *
    * @var integer
    */
   protected $_minCountValue = 0;

   /**
    * Minimum length of word to be displayed
    *
    * @var integer
    */
   protected $_minWordLen = 2;

   /**
    * Maximum length of a word to be displayed
    *
    * @var integer
    */
   protected $_maxWordLen = 30;

   /**
    * Minimum font size of a tag to be displayed
    *
    * @var integer
    */
   protected $_minFontSize = 30;

   /**
    * Maximum font size of a tag to be displayed
    *
    * @var integer
    */
   protected $_maxFontSize = 400;

   /**
    * Maximum number of style class to use
    *
    * @var integer
    */
   protected $_styleclass = 5;

   /**
    * Sort method
    * (alphabetical, count, random)
    *
    * @var string
    */
   protected $_sort = 'alphabetical';

   /**
    * Filter numeric words
    *
    * @var boolean
    */
   protected $_filter_numeric = TRUE;

   /**
    * Normalize tag cloud
    *
    * @var boolean
    */
   protected $_normalize = TRUE;

   /**
    * Words to not be displayed
    *
    * @var array
    */
   protected $_exclude_words = array ();

   /**
    * Change search URL variable
    *
    * @var string
    */
   protected $_searchVariable = 'search';

   /**
    * Tagclouds title
    *
    * @var string
    */
   protected $_cloudsTitle = '';

   /**
    * Encoding
    *
    * @var string
    */
   protected $_encoding = 'UTF-8';

   /**
    * Full HTML output
    *
    * @var string
    */
   protected $output = '';

   /**
    * Build tags output and return it
    *
    * @return string
    */
   public function get_tagclouds ()
   {
      //Detect current encoding
      $this->_encoding = mb_strtoupper (mb_detect_encoding ($this->_tags_string, 'auto', TRUE));

      //Convert to UTF-8 if needed
      if ('UTF-8' !== $this->_encoding)
      {
         $this->_tags_string = $this->toUTF8 ($this->_tags_string);

         //Detect new encoding
         $this->_encoding = mb_strtoupper (mb_detect_encoding ($this->_tags_string, 'auto', TRUE));
      }

      //Clean string
      $this->_tags_string = $this->clean_tagstring ($this->_tags_string);

      //Build array of tags from string
      $this->build_tags_array ($this->_tags_string);

      //Build the output
      $this->build_output ();

      //Make output
      return $this->output;
   }

   /**
    * Define maximum number of allowed tags
    *
    * @param  integer (zero or NULL mean infinite)
    */
   public function maxWordsCount ($val)
   {
      //Validate and save value
      if (empty ($val))
      {
         $this->_maxWordsCount = null;
      }

      $this->_maxWordsCount = is_numeric ($val) ? intval ($val) : $this->_minCountValue;
   }

   /**
    * Change minimum occurrences of a word to be displayed
    *
    * @param  integer
    */
   public function mincount ($val)
   {
      //Validate and save value
      $this->_minCountValue = is_numeric ($val) ? intval ($val) : $this->_minCountValue;
   }

   /**
    * Change minimum length of a word to be displayed
    *
    * @param  integer
    */
   public function minWordLength ($val)
   {
      //Validate and save value
      $this->_minWordLen = is_numeric ($val) ? intval ($val) : $this->_minWordLen;
   }

   /**
    * Change maximum length of a word to be displayed
    *
    * @param  integer
    */
   public function maxWordLength ($val)
   {
      //Validate and save value
      $this->_maxWordLen = is_numeric ($val) ? intval ($val) : $this->_maxWordLen;
   }

   /**
    * Change minimum font size of a tag to be displayed
    *
    * @param  integer
    */
   public function minfontsize ($val)
   {
      //Validate and save value
      $this->_minFontSize = is_numeric ($val) ? intval ($val) : $this->_minFontSize;
   }

   /**
    * Change maximum font size of a tag to be displayed
    *
    * @param  integer
    */
   public function maxfontsize ($val)
   {
      //Validate and save value
      $this->_maxFontSize = is_numeric ($val) ? intval ($val) : $this->_maxFontSize;
   }

   /**
    * Define maximum nuber for element class (CSS styling)
    *
    * @param  integer
    */
   public function styleclass ($val)
   {
      //Validate and save value
      $this->_styleclass = is_numeric ($val) ? intval ($val) : $this->_styleclass;
   }

   /**
    * Maximum number of style class to use
    *
    * @param  integer
    */
   public function sortmethod ($val)
   {
      //Correct value
      $val = strtolower (trim ($val));

      //Define supported methods for sorting
      $allowedSortMethods = array (

         'alphabetical' ,
         'count' ,
         'random'
      );

      //Validate and save value
      $this->_sort = in_array ($val, $allowedSortMethods) ? $val : 'alphabetical';
   }

   /**
    * Change search URL variable
    *
    * @param  string
    */
   public function setsearchvariable ($variable = 'search')
   {
      //Save value
      $this->_searchVariable = trim ($variable);
   }

   /**
    * Filter numeric words
    *
    * @param  boolean
    */
   public function filter_numeric ($filter = TRUE)
   {
      $this->_filter_numeric = (boolean) $filter;
   }

   /**
    * Normalize tag cloud
    *
    * @param  boolean
    */
   public function normalize ($normalize = TRUE)
   {
      $this->_normalize = (boolean) $normalize;
   }

   /**
    * Change title for tagclouds
    *
    * @param  string
    */
   public function title ($title = '')
   {
      //Save value
      $this->_cloudsTitle = trim ($title);
   }

   /**
    * Add more words to not be displayed
    *
    * @param  mixed (Array or string)
    * @return boolean TRUE on success, FALSE on error
    */
   public function exclude ($exclude)
   {
      if (empty ($exclude))
      {
         //Nothing to do
         return FALSE;
      }
      else
      {
         if (is_array ($exclude)) //If passed variable is an array, process it as an array
         {
            //Merge with existing exclusion words array
            $this->_exclude_words = array_merge ($this->_exclude_words, $exclude);
         }
         elseif (is_string ($exclude)) //If passed variable is a string, process it as string
         {
            //Clean string
            $exclude = $this->strip_specialchars ($exclude);

            //Convert string to array
            $exclude = explode (' ', $exclude);

            //Merge with existing exclusion words array
            if (is_array ($exclude) && ! empty ($exclude))
            {
               $this->_exclude_words = array_merge ($this->_exclude_words, $exclude);
            }
         }
      }

      return TRUE;
   }

   /**
    * Process categories
    *
    * @param  mixed (Array or string)
    * @return boolean TRUE on success, FALSE on error
    */
   public function add_categories ($categories)
   {
      if (empty ($categories))
      {
         //Nothing to do
         return FALSE;
      }
      else
      {
         //If passed variable is an array, process it as an array
         if (is_array ($categories))
         {
            //Process each category at a time
            foreach ($categories as $key => $categ)
            {
               //Add category title text
               if (! empty ($categ['TITLE']))
               {
                  $this->_tags_string .= ' ' . $categ['TITLE'];
               }

               //Add category description text
               if (! empty ($categ['DESCRIPTION']))
               {
                  $this->_tags_string .= ' ' . $categ['DESCRIPTION'];
               }

               //Process subdirectories if available
               if (isset ($categ['SUBCATS']) && is_array ($categ['SUBCATS']) && ! empty ($categ['SUBCATS']))
               {
                  //Process each subcategory at a time
                  foreach ($categ['SUBCATS'] as $subcateg)
                  {
                     //Add subcategory title text
                     if (! empty ($subcateg['TITLE']))
                     {
                        $this->_tags_string .= ' ' . $subcateg['TITLE'];
                     }

                     //Add subcategory description text
                     if (! empty ($subcateg['DESCRIPTION']))
                     {
                        $this->_tags_string .= ' ' . $subcateg['DESCRIPTION'];
                     }

                     //Free memory
                     unset ($subcateg, $categ['SUBCATS'][$key]);
                  }
               }

               //Free memory
               unset ($categ, $categories[$key]);
            }
         }
         elseif (is_string ($categories)) //If passed variable is a string, process it as string
         {
            //Add category text
            $this->_tags_string .= ' ' . $categories;
         }
         //Free memory
         unset ($categories);
      }

      return TRUE;
   }

   /**
    * Process links
    *
    * @param  mixed (Array or string)
    * @return boolean TRUE on success, FALSE on error
    */
   public function add_links ($links)
   {
      if (empty ($links))
      {
         //Nothing to do
         return FALSE;
      }
      else
      {
         //If passed variable is an array, process it as an array
         if (is_array ($links))
         {
            //Process each link at a time
            foreach ($links as $key => $link)
            {
               //Add link title text
               if (! empty ($link['TITLE']))
               {
                  $this->_tags_string .= ' ' . $link['TITLE'];
               }

               //Add link description text
               if (! empty ($link['DESCRIPTION']))
               {
                  $this->_tags_string .= ' ' . $link['DESCRIPTION'];
               }

               //Free memory
               unset ($link, $links[$key]);
            }
         }
         elseif (is_string ($links)) //If passed variable is a string, process it as string
         {
            //Add link text
            $this->_tags_string .= ' ' . $links;
         }
         //Free memory
         unset ($links);
      }

      return TRUE;
   }

   /**
    * Process articles
    *
    * @param  mixed (Array or string)
    * @return boolean TRUE on success, FALSE on error
    */
   function add_articles ($articles)
   {
      if (empty ($articles))
      {
         //Nothing to do
         return FALSE;
      }
      else
      {
         //If passed variable is an array, process it as an array
         if (is_array ($articles))
         {
            //Process each link at a time
            foreach ($articles as $key => $article)
            {
               //Add article title text
               if (! empty ($article['TITLE']))
               {
                  $this->_tags_string .= ' ' . $article['TITLE'];
               }

               //Add article body text
               if (! empty ($article['ARTICLE']))
               {
                  $this->_tags_string .= ' ' . $article['ARTICLE'];
               }

               //Add article description text
               if (! empty ($article['DESCRIPTION']))
               {
                  $this->_tags_string .= ' ' . $article['DESCRIPTION'];
               }

               //Free memory
               unset ($article, $articles[$key]);
            }
         }
         elseif (is_string ($article)) //If passed variable is a string, process it as string
         {
            //Add link text
            $this->_tags_string .= ' ' . $article;
         }

         //Free memory
         unset ($article);
      }

      return TRUE;
   }

   /**
    * Clean tag string of unneeded characters
    * (This will also escape the string)
    *
    * @param  string
    * @return string
    */
   protected function clean_tagstring ($string = '')
   {
      //Remove HTML and PHP tags
      $string = strip_tags ($string);

      //Strip whitespace
      $string = $this->strip_specialchars ($string);

      //Convert special characters to HTML entities
      $string = $this->escape ($string);

      //Make lowercase and trim
      $string = mb_strtolower ($string, $this->_encoding);

      return trim ($string);
   }

   /**
    * Clean string of whitespace and special characters
    *
    * @param  string
    * @return string
    */
   protected function strip_specialchars ($string = '')
   {
      //Handle whitespace
      $string = str_replace ("&nbsp;", " ", $string); //Windows
      $string = str_replace ("\r\n", " ", $string); //Windows
      $string = str_replace ("\r", " ", $string); //Mac
      $string = str_replace ("\n", " ", $string); //*NIX
      $string = str_replace ("\t", " ", $string); //TAB
      $string = str_replace ("\0", "", $string); //NULL BYTE
      $string = str_replace ("\x0B", "", $string); //Vertical TAB


      //Remove anything except word, digit characters and spaces, at the end remove multiple spaces
      $pattern = array (

         '#[\'"/\.,_\-()!?@\#$%^&*+:;=`<>\\\]#i' ,  //special chars
         '#[\s]{2,}#' //multiple white-spaces
      );

      $string = preg_replace ($pattern, ' ', $string);

      return trim ($string);
   }

   /**
    * Convert special characters to HTML entities
    *
    * @param  string String to be escaped
    * @return string Escaped string
    */
   protected function escape ($string = '')
   {
      return htmlspecialchars ($string, ENT_COMPAT, $this->_encoding);
   }

   /**
    * Converts strings to UTF-8 via iconv. NB, the result may not by UTF-8 if the conversion failed.
    *
    * This file comes from Prado (BSD License)
    *
    * @param  string $string string to convert to UTF-8
    * @param  string $from   current encoding
    * @return string UTF-8 encoded string, original string if iconv failed
    */
   protected function toUTF8 ($string, $from)
   {
      $from = mb_strtoupper ($from);

      if ($from !== 'UTF-8')
      {
         // to UTF-8
         $s = iconv ($from, 'UTF-8', $string);

         // it could return FALSE
         return $s !== FALSE ? $s : $string;
      }

      return $string;
   }

   /**
    * Normalizes a tag cloud, ie. changes a (tag => weight) array into a
    * (tag => normalized_weight) one.
    * Normalized weights range from -2 to 2.
    *
    * @copyright 2007 Xavier Lacot (sfPropelActAsTaggableBehaviorPlugin Plugin for Symfony project)
    * @copyright 2007 Michael Nolan (sfPropelActAsTaggableBehaviorPlugin Plugin for Symfony project)
    *
    * @deprecated
    *
    * @param  array  $tag_cloud
    * @return array
    */
   protected function normalize_tag_cloud ($tag_cloud)
   {
      //Make sure tag cloud array is an array
      $tag_cloud = (array) $tag_cloud;

      $tags = array ();
      $levels = 5;
      $power = 0.7;

      if (count ($tag_cloud) > 0)
      {
         $max_count = max ($tag_cloud);
         $min_count = min ($tag_cloud);
         $max = intval ($levels / 2);

         if ($max_count != 0)
         {
            foreach ($tag_cloud as $tag => $count)
            {
               $tag = (string) $tag;
               $count = (int) $count;

               $tags[$tag] = (int) round (.9999 * $levels * (pow ($count / $max_count, $power) - .5), 0);
            }
         }
      }

      return (array) $tags;
   }

   /**
    * Build array with tags
    *
    * @param  string
    * @return boolean TRUE on success, FALSE on error (=empty tags string)
    */
   protected function build_tags_array ($string = '')
   {
      if (empty ($string))
      {
         return FALSE;
      }
      else
      {
         //Split string into array
         $array = explode (' ', $string);

         //Drop empty values
         $array = array_filter ($array);

         //Count words
         $array_content = array_count_values ($array);

         //Process each word at a time
         foreach ($array_content as $word => $count)
         {
            //Check if word is numeric
            if (TRUE === $this->_filter_numeric && is_numeric ($word))
            {
               continue;
            }

            //Check minimum count value
            if ($count < $this->_minCountValue)
            {
               continue;
            }

            //Check word length
            if (mb_strlen ($word) < $this->_minWordLen || mb_strlen ($word) > $this->_maxWordLen)
            {
               continue;
            }

            //Check if in exclude words
            if (in_array ($word, $this->_exclude_words))
            {
               continue;
            }

            //Word is OK, add to new array
            $this->_tags[$word] = $count;

            //Free memory
            unset ($count, $array_content[$word]);
         }

         //Check if we need to normalize tag cloud
         if (TRUE === $this->_normalize)
         {
            //Normalize
         //$this->_tags = $this->normalize_tag_cloud ($this->_tags);
         }

         //Sort in reverse order (highest counts first)
         //Sortmethod = count
         arsort ($this->_tags);

         if (! empty ($this->_maxWordsCount))
         {
            //Truncate array to maximum allowed words count
            $this->_tags = array_slice ($this->_tags, 0, $this->_maxWordsCount, TRUE);
         }

         switch ($this->_sort)
         {
            case 'alphabetical':
               //Sort words alphabetically
               ksort ($this->_tags, SORT_STRING);
               break;

            case 'random':
               //Shuffle arrray (random order)
               //Workaround to shuffle array because the "shuffle()" function
               //assigns new keys for the elements in array
               $keys = array_keys ($this->_tags);
               shuffle ($keys);

               $newTags = array ();
               foreach ($keys as $key)
               {
                  $newTags[$key] = $this->_tags[$key];
               }
               $this->_tags = $newTags;
               unset ($newTags, $keys);
               break;

            default:
               //Nothing to do, array is already sorted by words count
               break;
         }
      }

      return TRUE;
   }

   /**
    * Build HTML output
    *
    * @param  string
    * @return boolean TRUE on success ($output populated), FALSE on error (no tags/words available)
    */
   protected function build_output ()
   {
      if (! is_array ($this->_tags) || empty ($this->_tags))
      {
         //No tags/word, return empty output
         $this->output = '';

         return FALSE;
      }
      else
      {
         //Determine maximum occurrence value
         $maxHits = max ($this->_tags);

         //Determine minimum and maximum occurences
         $minOccurs = min ($this->_tags);
         $maxOccurs = max ($this->_tags);

         //Add module details (please keep this)
         $this->add_module_details ();

         //Add tagclouds box
         $this->output .= '<div class="tagclouds">';

         //Add a title if available
         if (! empty ($this->_cloudsTitle))
         {
            $this->output .= $this->_cloudsTitle;
         }

         //Add the tagclouds paragraph
         $this->output .= '<p>';

         //Process each tag
         foreach ($this->_tags as $word => $count)
         {
            //Calculate font-size for current tag
            $weight = (log ($count) - log ($minOccurs)) / (log ($maxOccurs) - log ($minOccurs));
            $fontSize = intval ($this->_minFontSize + round (($this->_maxFontSize - $this->_minFontSize) * $weight));

            //Calculate style class (usually for CSS colors)
            $styleClass = ceil (((($count - $this->_minCountValue) * 100) / ($maxHits - $this->_minCountValue)) / (100 / $this->_styleclass));

            //Add word to output
            $this->output .= '<a href="' . DOC_ROOT . '/index.php?' . $this->_searchVariable . '=' . $word . '" style="font-size:' . $fontSize . '%;" class="cloud-word cloud-style-' . $styleClass . '" title="' . $word . '">' . $word . '</a> ';

            //Free memory
            unset ($this->_tags[$word], $word, $count, $fontSize, $styleClass);
         }

         //Trim output (last char is a space)
         $this->output = trim ($this->output);

         //Close paragraph and box
         $this->output .= '</p></div>';
      }

      return TRUE;
   }

   /**
    * Build HTML module information output
    *
    * (!! please maintain this section !!)
    */
   public function add_module_details ()
   {
      $this->output = "\n<!--";
      $this->output .= "\n# Project:  PHP Link Directory - Link exchange directory @ http://www.phplinkdirectory.com/";
      $this->output .= "\n# Module:   phpLD Simple TagCloud";
      $this->output .= "\n# Homepage: http://www.frozenminds.com/phpld-tagclouds.html";
      $this->output .= "\n# Author:   Constantin Bejenaru aKa Boby @ http://www.frozenminds.com/";
      $this->output .= "\n# Language: PHP + xHTML + CSS";
      $this->output .= "\n# License:  GNU/GPL";
      $this->output .= "\n-->\n";
   }
}
