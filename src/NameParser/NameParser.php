<?php

namespace FullNameParser\NameParser;

/**
 * Split a full name into its constituent parts
 *   - prefix/salutation (Mr. Mrs. Dr. etc)
 *   - given/first name
 *   - middle name/initial(s)
 *   - surname (last name)
 *   - surname base (last name without compounds)
 *   - surname compounds (only the compounds)
 *   - suffix (II, PhD, Jr. etc)
 *
 * Author: Josh Fraser
 *
 * Contribution from Clive Verrall www.cliveverrall.com February 2016
 *
 * // other contributions:
 * //   - eric willis [list of honorifics](http://notes.ericwillis.com/2009/11/common-name-prefixes-titles-and-honorifics/)
 * //   - `TomThak` for raising issue #16 and providing [wikepedia resource](https://cs.wikipedia.org/wiki/Akademick%C3%BD_titul)
 * //   - `atla5` for closing the issue.
 */
class NameParser {

    private $parsedName = array(
        'title' => '',
        'first' => '',
        'middle' => '',
        'last' => '',
        'nick' => '',
        'suffix' => ''
    );

    /**
     * Create the dictionary of terms for use later
     *
     *  - Common honorific prefixes (english)
     *  - Common compound surname identifiers
     *  - Common suffixes (lineage and professional)
     * Note: longer professional titles should appear earlier in the array than shorter titles to reduce the risk of mis-identification e.g. BEng before BE
     * Also note that case and periods are part of the matching for professional titles and therefore need to be correct, there are no case conversions
     */
    protected $suffixList = array('esq', 'esquire', 'jr', 'jnr', 'sr', 'snr', '2', 'ii', 'iii', 'iv',
        'v', 'clu', 'chfc', 'cfp', 'md', 'phd', 'j.d.', 'll.m.', 'm.d.', 'd.o.', 'd.c.',
        'p.c.', 'ph.d.');
    protected $prefixList = array('a', 'ab', 'antune', 'ap', 'abu', 'al', 'alm', 'alt', 'bab', 'bäck',
        'bar', 'bath', 'bat', 'beau', 'beck', 'ben', 'berg', 'bet', 'bin', 'bint', 'birch',
        'björk', 'björn', 'bjur', 'da', 'dahl', 'dal', 'de', 'degli', 'dele', 'del',
        'della', 'der', 'di', 'dos', 'du', 'e', 'ek', 'el', 'escob', 'esch', 'fleisch',
        'fitz', 'fors', 'gott', 'griff', 'haj', 'haug', 'holm', 'ibn', 'kauf', 'kil',
        'koop', 'kvarn', 'la', 'le', 'lind', 'lönn', 'lund', 'mac', 'mhic', 'mic', 'mir',
        'na', 'naka', 'neder', 'nic', 'ni', 'nin', 'nord', 'norr', 'ny', 'o', 'ua', 'ui\'',
        'öfver', 'ost', 'över', 'öz', 'papa', 'pour', 'quarn', 'skog', 'skoog', 'sten',
        'stor', 'ström', 'söder', 'ter', 'ter', 'tre', 'türk', 'van', 'väst', 'väster',
        'vest', 'von');
    protected $titleList = array('mr', 'mrs', 'ms', 'miss', 'dr', 'herr', 'monsieur', 'hr', 'frau',
        'a v m', 'admiraal', 'admiral', 'air cdre', 'air commodore', 'air marshal',
        'air vice marshal', 'alderman', 'alhaji', 'ambassador', 'baron', 'barones',
        'brig', 'brig gen', 'brig general', 'brigadier', 'brigadier general',
        'brother', 'canon', 'capt', 'captain', 'cardinal', 'cdr', 'chief', 'cik', 'cmdr',
        'coach', 'col', 'col dr', 'colonel', 'commandant', 'commander', 'commissioner',
        'commodore', 'comte', 'comtessa', 'congressman', 'conseiller', 'consul',
        'conte', 'contessa', 'corporal', 'councillor', 'count', 'countess',
        'crown prince', 'crown princess', 'dame', 'datin', 'dato', 'datuk',
        'datuk seri', 'deacon', 'deaconess', 'dean', 'dhr', 'dipl ing', 'doctor',
        'dott', 'dott sa', 'dr', 'dr ing', 'dra', 'drs', 'embajador', 'embajadora', 'en',
        'encik', 'eng', 'eur ing', 'exma sra', 'exmo sr', 'f o', 'father',
        'first lieutient', 'first officer', 'flt lieut', 'flying officer', 'fr',
        'frau', 'fraulein', 'fru', 'gen', 'generaal', 'general', 'governor', 'graaf',
        'gravin', 'group captain', 'grp capt', 'h e dr', 'h h', 'h m', 'h r h', 'hajah',
        'haji', 'hajim', 'her highness', 'her majesty', 'herr', 'high chief',
        'his highness', 'his holiness', 'his majesty', 'hon', 'hr', 'hra', 'ing', 'ir',
        'jonkheer', 'judge', 'justice', 'khun ying', 'kolonel', 'lady', 'lcda', 'lic',
        'lieut', 'lieut cdr', 'lieut col', 'lieut gen', 'lord', 'm', 'm l', 'm r',
        'madame', 'mademoiselle', 'maj gen', 'major', 'master', 'mevrouw', 'miss',
        'mlle', 'mme', 'monsieur', 'monsignor', 'mstr', 'nti', 'pastor',
        'president', 'prince', 'princess', 'princesse', 'prinses', 'prof', 'prof dr',
        'prof sir', 'professor', 'puan', 'puan sri', 'rabbi', 'rear admiral', 'rev',
        'rev canon', 'rev dr', 'rev mother', 'reverend', 'rva', 'senator', 'sergeant',
        'sheikh', 'sheikha', 'sig', 'sig na', 'sig ra', 'sir', 'sister', 'sqn ldr', 'sr',
        'sr d', 'sra', 'srta', 'sultan', 'tan sri', 'tan sri dato', 'tengku', 'teuku',
        'than puying', 'the hon dr', 'the hon justice', 'the hon miss', 'the hon mr',
        'the hon mrs', 'the hon ms', 'the hon sir', 'the very rev', 'toh puan', 'tun',
        'vice admiral', 'viscount', 'viscountess', 'wg cdr', 'ind', 'misc', 'mx');

    protected $conjunctionList = array('&', 'and', 'et', 'e', 'of', 'the', 'und', 'y');

    protected $nameParts = array();

    /**
     * Parse Static entry point.
     *
     * @param string $name the full name you wish to parse
     * @return array returns associative array of name parts
     */
    public static function parseFullName($name) {
        $parser = new self();

        return $parser->handleParse($name);
    }

    private function handleParse($nameToParse) {
        $partsFound = array();

        preg_match_all("/\s(?:[‘’']([^‘’']+)[‘’']|[“”\"]([^“”\"]+)[“”\"]|\[([^\]]+)\]|\(([^\)]+)\)),?\s/", ' ' . $nameToParse . ' ', $partFound);

        if ($partFound) {
            $partsFound = array_merge($partsFound, $partFound[0]);
        }

        $partsFoundCount = count($partsFound);

        if ($partsFoundCount === 1) {

            $this->parsedName['nick'] = substr(substr($partsFound[0], 2), 0, -2);

            if (substr($this->parsedName['nick'], -1) === ',') {
                $this->parsedName['nick'] = substr($this->parsedName['nick'], 0, -1);
            }
            $nameToParse = trim(str_replace($partsFound[0], ' ', ' ' . $nameToParse . ' '));

            $partsFound = array();

        } else if ($partsFoundCount > 1) {

            for ($i = 0; $i < $partsFoundCount; $i++) {
                $nameToParse = trim(str_replace($partsFound[$i], ' ', ' ' . $nameToParse . ' '));

                $partsFound[$i] = substr(substr($partsFound[$i][0], 2), 0, -2);

                if (substr($partsFound[$i], -1) === ',') {
                    $partsFound[$i] = substr($partsFound[$i][0], 0, -1);
                }
            }
            $this->parsedName['nick'] = implode(", ", $partsFound);
            $partsFound = array();
        }

        if (strlen(trim($nameToParse)) == 0) {
            $this->parsedName = $this->fixParsedNameCase($this->parsedName);
            return $this->parsedName;
        }

        $part = "";
        $comma = "";
        $nameCommas = array();

        $nameToParseArray = explode(" ", $nameToParse);
        // Split remaining nameToParse into parts, remove and store preceding commas
        for ($i = 0; $i < count($nameToParseArray); $i++) {
            $part = $nameToParseArray[$i];
            $comma = null;
            if (substr($part, -1) === ',') {
                $comma = ',';
                $part = substr($part, 0, -1);
            }
            $this->nameParts[] = $part;
            $nameCommas[] = $comma;
        }

        // Suffix: remove and store matching parts as suffixes
        for ($i = count($this->nameParts) - 1; $i > 0; $i--) {
            $partToCheck = (substr($this->nameParts[$i], -1) === '.' ?
                strtolower(substr($this->nameParts[$i], 0, -1)) : strtolower($this->nameParts[$i]));

            if (
                array_search($partToCheck, $this->suffixList) !== false  || array_search($partToCheck . ".", $this->suffixList) !== false
            ) {
                $partsFound = array_merge(array_splice($this->nameParts, $i, 1), $partsFound);

                if ($nameCommas[$i] === ',') { // Keep comma, either before or after
                    array_splice($nameCommas, $i + 1, 1);
                } else {
                    array_splice($nameCommas, $i, 1);
                }
            }
        }

        $partsFoundCount = count($partsFound);

        if ($partsFoundCount === 1) {
            $this->parsedName['suffix'] = $partsFound[0];
            $partsFound = array();
        } else if ($partsFoundCount > 1) {

            $this->parsedName['suffix'] = implode(", ", $partsFound);
            $partsFound = array();
        }
        if (count($this->nameParts) == 0) {
            $this->parsedName = $this->fixParsedNameCase($this->parsedName);
            return $this->parsedName;
        }

        // Title: remove and store matching parts as titles
        for ($i = count($this->nameParts) - 1; $i >= 0; $i--) {

            $partToCheck = (substr($this->nameParts[$i], -1) === '.' ?
                strtolower(substr($this->nameParts[$i], 0, -1)) : strtolower($this->nameParts[$i]));

            if (
                array_search($partToCheck, $this->titleList) !== false || array_search($partToCheck . ".", $this->titleList) !== false
            ) {
                $partsFound = array_merge(array_splice($this->nameParts, $i, 1), $partsFound);


                if ($nameCommas[$i] === ',') { // Keep comma, either before or after
                    array_splice($nameCommas, $i + 1, 1);
                } else {
                    array_splice($nameCommas, $i, 1);
                }
            }
        }

        $partsFoundCount = count($partsFound);

        if ($partsFoundCount === 1) {
            $this->parsedName['title'] = $partsFound[0];

            $partsFound = array();
        } else if ($partsFoundCount > 1) {

            $this->parsedName['title'] = implode(", ", $partsFound);
            $partsFound = array();
        }
        if (count($this->nameParts) == 0) {
            $this->parsedName = $this->fixParsedNameCase($this->parsedName);
            return $this->parsedName;
        }

        // Join name prefixes to following names
        if (count($this->nameParts) > 1) {
            for ($i = count($this->nameParts) - 2; $i >= 0; $i--) {
                if (array_search(strtolower($this->nameParts[$i]), $this->prefixList) !== false) {
                    $this->nameParts[$i] = $this->nameParts[$i] . ' ' . $this->nameParts[$i + 1];
                    array_splice($this->nameParts, $i + 1, 1);
                    array_splice($nameCommas, $i + 1, 1);
                }
            }
        }

        // Join conjunctions to surrounding names
        if (count($this->nameParts) > 2) {
            for ($i = count($this->nameParts) - 3; $i >= 0; $i--) {
                if (array_search(strtolower($this->nameParts[$i + 1]), $this->conjunctionList)) {
                    $this->nameParts[$i] = $this->nameParts[$i] . ' ' . $this->nameParts[$i + 1] . ' ' . $this->nameParts[$i + 2];
                    array_splice($this->nameParts, $i + 1, 2);
                    array_splice($nameCommas, $i, 2);
                    $i--;
                }
            }
        }

        // Suffix: remove and store items after extra commas as suffixes
        array_pop($nameCommas);

        $firstComma = array_search(",", $nameCommas);
        $remainingCommas = count(array_filter($nameCommas, 'strlen'));

        if ($firstComma > 1 || $remainingCommas > 1) {
            for ($i = count($this->nameParts) - 1; $i >= 2; $i--) {
                if ($nameCommas[$i] === ',') {
                    $partsFound = array_merge(array_splice($this->nameParts, $i, 1), $partsFound);
                    arrray_splice($nameCommas, $i, 1);
                    $remainingCommas--;
                } else {
                    break;
                }
            }
        }
        if (count($partsFound)) {
            if ($this->parsedName['suffix']) {
                $partsFound = array_merge(array($this->parsedName['suffix']), $partsFound);
            }
            $this->parsedName['suffix'] = implode(", ", $partsFound);
            $partsFound = array();
        }

        // Last name: remove and store last name

        if ($remainingCommas > 0) {
            if ($remainingCommas > 1) {
                return;
            }
            // Remove and store all parts before first comma as last name
            if (array_search(",", $nameCommas) !== false) {
                $this->parsedName['last'] = implode(' ', array_splice($this->nameParts, 0, array_search(',', $nameCommas) + 1));

                array_splice($nameCommas, 0, array_search(',', $nameCommas));
            }
        } else {
            // Remove and store last part as last name
            $this->parsedName['last'] = array_pop($this->nameParts);
        }

        if (count($this->nameParts) == 0) {
            $this->parsedName = $this->fixParsedNameCase($this->parsedName);
            return $this->parsedName;
        }

        // First name: remove and store first part as first name
        $this->parsedName['first'] = array_shift($this->nameParts);

        if (count($this->nameParts) == 0) {
            $this->parsedName = $this->fixParsedNameCase($this->parsedName);
            return $this->parsedName;
        }


        // Middle name: store all remaining parts as middle name
        $this->parsedName['middle'] = implode(' ', $this->nameParts);
        $this->parsedName = $this->fixParsedNameCase($this->parsedName);

        return $this->parsedName;
    }

    private function fixParsedNameCase($fixedCaseName) {
        $forceCaseList = array('e', 'y', 'av', 'af', 'da', 'dal', 'de', 'del', 'der', 'di',
            'la', 'le', 'van', 'der', 'den', 'vel', 'von', 'II', 'III', 'IV', 'J.D.', 'LL.M.',
            'M.D.', 'D.O.', 'D.C.', 'Ph.D.');

        $mcCase = array('mc', 'mac', 'de', 'o\'');

        $namePartWords = array();
        $namePartsKeys = array_keys($this->parsedName);

        foreach ($this->parsedName as $key => $value) {

            if ($fixedCaseName[$key]) {

                $namePartWords = explode(' ', $fixedCaseName[$key]);

                for ($i = 0; $i < count($namePartWords); $i++) {

                    $lowerCaseList = array_map('strtolower', $forceCaseList);
                    $forceCaseListIndex = array_search(strtolower($namePartWords[$i]), $lowerCaseList);

                    if ($forceCaseListIndex !== false) { // Set case of words in forceCaseList
                        $namePartWords[$i] = $forceCaseList[$forceCaseListIndex];
                    } else if (strlen($namePartWords[$i]) === 1) { // Uppercase initials
                        $namePartWords[$i] = strtoupper($namePartWords[$i]);
                    } else
                        if (
                            strlen($namePartWords[$i]) > 2 &&
                            substr($namePartWords[$i], 0, 1) === strtoupper(substr($namePartWords[$i], 0, 1)) &&
                            substr($namePartWords[$i], 1, 2) === strtolower(substr($namePartWords[$i], 1, 2)) &&
                            substr($namePartWords[$i], 2) === strtoupper(substr($namePartWords[$i], 2))
                        ) { // Detect McCASE and convert to McCase
                            $namePartWords[$i] = substr($namePartWords[$i], 0, 3) .
                                strtolower(substr($namePartWords[$i], 3));
                        } else if (
                            $namePartsKeys[$i] === 'suffix' &&
                            substr($this->nameParts[$i], -1) !== '.' &&
                            array_search(strtolower($this->nameParts[$i]), $this->suffixList) === false
                        ) { // Convert suffix abbreviations to UPPER CASE
                            if ($namePartWords[$i] === strtolower($namePartWords[$i])) {
                                $namePartWords[$i] = strtoupper($namePartWords[$i]);
                            }
                        } else { // Convert to Title Case
                            $namePartWords[$i] = strtoupper(substr($namePartWords[$i], 0, 1)) .
                                strtolower(substr($namePartWords[$i], 1));
                            // Detect mccase and convert to McCase
                            if (array_search(strtolower(substr($namePartWords[$i], 0, 2)), $mcCase) !== false) {
                                $namePartWords[$i] = strtoupper(substr($namePartWords[$i], 0, 1)) .
                                    substr($namePartWords[$i], 1, 1) .
                                    strtoupper(substr($namePartWords[$i], 2, 1)) .
                                    substr($namePartWords[$i], 3);
                            }

                            if (array_search(strtolower(substr($namePartWords[$i], 0, 3)), $mcCase) !== false) {
                                $namePartWords[$i] = strtoupper(substr($namePartWords[$i], 0, 1)) .
                                    substr($namePartWords[$i], 1, 2) .
                                    strtoupper(substr($namePartWords[$i], 3, 1)) .
                                    substr($namePartWords[$i], 4);
                            }
                        }
                }
                $fixedCaseName[$key] = implode(" ", $namePartWords);
            }
        }

        return $fixedCaseName;
    }

}
