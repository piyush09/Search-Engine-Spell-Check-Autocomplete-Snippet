Spell Checking

1. Spell Checking feature is implemented, I used the Peter Norvig’s Spelling corrector code in PHP to find incorrect words.
2. Peter Norvig’s program has requirement of big.txt file as input file, which contains all the terms in the inverted index of the Search Engine.
3. The search component was included in the solrconfig.xml file as described in the assignment description.
4. Beautiful Soup, Html2Text and lxml were used to parse the given html files and then passed to enchant dictionary, to check if a given word is a valid English word.
5. Parser.py was used and it parsed the Html to generate big.txt.
6. SpellCorrector.php was run to check working of SpellCorrector program, which thus generated the serialized_dictionary.txt.
7. There is a correct() function in SpellCorrector program, which outputs the correct form of wrong word.
8. The spell corrector program, outputs the correct word in it and in the UI option, it is given to choose the correct results for corrected word or incorrect word.

Autocomplete:

1. As in Spellcheck component, similarly a suggest component is included in the solrconfig.xml in order to get the autocomplete part in the query.
2. There was an addition of searchcomponent with name as suggest and with string names as name, lookupImpl, field, suggestAnalyzerFieldType in it.
3. A php-client using the object of apache class used the request handler suggest instead of default search request handler.
4. The links are clickable and they will auto complete the word and result is shown in search box.
5. Hence, an autocomplete word is shown. The default value for the number of suggestions is set to 5.

Snippet:
1. "simple_html_dom" parser is used to generate the plaintext of the given html pages.
2. The string is filtered and special characters are removed from it.
3. String matching is performed to get the index of word in query.
4. So, the snippet is found and it is thus outputted on the web page.

The spellcheck and auto suggest components are integrated in the php code.

