## Description

parseFullName() is designed to parse large batches of full names in multiple
inconsistent formats, as from a database, and continue processing without error,
even if given some unparsable garbage entries.

parseFullName("MACDONALT, Mr. JÜAN MARTINEZ (MARTIN) Jr."):

<pre>
array:6 [
  "title" => "Mr."
  "first" => "JÜan"
  "middle" => "Martinez"
  "last" => "MacDonalt"
  "nick" => "Martin"
  "suffix" => "Jr."
]
</pre>

1. accepts a string containing a person's full name, in any format,
2. analyzes and attempts to detect the format of that name,
3. (if possible) parses the name into its component parts, and
4. (by default) returns an object containing all individual parts of the name:
    - title (string): title(s) (e.g. "Ms." or "Dr.")
    - first (string): first name or initial
    - middle (string): middle name(s) or initial(s)
    - last (string): last name or initial
    - nick (string): nickname(s)
    - suffix (string): suffix(es) (e.g. "Jr.", "II", or "Esq.")

Optionally, parseFullName() can also:

* return only the specified part of a name as a string (or errors as an array)
* always fix or ignore the letter case of the returned parts (the default is
    to fix the case only when the original input is all upper or all lowercase)
* stop on errors (the default is to return warning messages in the output,
    but never throw a JavaScript error, no matter how mangled the input)
* detect more variations of name prefixes, suffixes, and titles (the default
    detects 29 prefixes, 19 suffixes, 16 titles, and 8 conjunctions, but it
    can be set to detect 97 prefixes, 23 suffixes, and 204 titles instead)

If this is not what you're looking for, is overkill for your application, or
is in the wrong language, check the "Credits" section at the end of this file
for links to several other excellent parsers which may suit your needs better.

## Credits and precursors

Before creating this function I studied many other name-parsing functions.
None quite suited my needs, but many are excellent at what they do, and
this function uses ideas from several of them.

My thanks to all the following developers for sharing their work.

David Schnell-Davis's parse-full-name javascript plugin:
https://github.com/dschnelldavis/parse-full-name

Josh Fraser's PHP-Name-Parser:
https://github.com/joshfraser/PHP-Name-Parser

Josh Fraser's JavaScript-Name-Parser:
https://github.com/joshfraser/JavaScript-Name-Parser

Garve Hays' Java NameParser:
https://github.com/gkhays/NameParser

Jason Priem's PHP HumanNameParser:
https://web.archive.org/web/20150408022642/http://jasonpriem.org/human-name-parse/ and
https://github.com/jasonpriem/HumanNameParser.php

Keith Beckman's PHP nameparse:
http://alphahelical.com/code/misc/nameparse/

Jed Hartman's PHP normalize_name:
http://www.kith.org/journals/jed/2007/02/11/3813.html and
http://www.kith.org/logos/things/code/name-parser-php.html

ashaffer88's JavaScript parse-name:
https://github.com/weo-edu/parse-name and
https://www.npmjs.com/package/parse-name

Derek Gulbranson's Python nameparser:
https://github.com/derek73/python-nameparser/

Discussion about how to change all upper or lowercase names to correct case:
http://stackoverflow.com/questions/11529213/given-upper-case-names-transform-to-proper-case-handling-ohara-mcdonald

Title lists modified from:
http://www.codeproject.com/Questions/262876/Titles-or-Salutation-list

Suffix lists modified from:
http://en.wikipedia.org/wiki/Suffix_(name) and
https://github.com/derek73/python-nameparser/blob/master/nameparser/config/suffixes.py

Prefix lists modified from:
http://en.wikipedia.org/wiki/List_of_family_name_affixes

Conjunction list copied entirely from:
https://github.com/derek73/python-nameparser/blob/master/nameparser/config/conjunctions.py