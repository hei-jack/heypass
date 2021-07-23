<?php
/**
 * This file is a part of "furqansiddiqui/bip39-mnemonics-php" package.
 * https://github.com/furqansiddiqui/bip39-mnemonics-php
 *
 * Copyright (c) 2019 Furqan A. Siddiqui <hello@furqansiddiqui.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code or visit following link:
 * https://github.com/furqansiddiqui/bip39-mnemonics-php/blob/master/LICENSE
 */

// declare(strict_types=1);

namespace FurqanSiddiqui\BIP39;

use FurqanSiddiqui\BIP39\Exception\MnemonicException;
use FurqanSiddiqui\BIP39\Exception\WordListException;

/**
 * Class BIP39
 * @package FurqanSiddiqui\BIP39
 */
class BIP39
{
    /** @var int */
    private $wordsCount;
    /** @var int */
    private $overallBits;
    /** @var int */
    private $checksumBits;
    /** @var int */
    private $entropyBits;
    /** @var null|string */
    private $entropy;
    /** @var null|array */
    private $rawBinaryChunks;

    /** @var null|WordList */
    private $wordList;

    /**
     * @param string $entropy
     * @return Mnemonic
     * @throws MnemonicException
     * @throws WordListException
     */
    public static function Entropy($entropy){
        self::validateEntropy($entropy);

        $entropyBits = strlen($entropy) * 4;
        $checksumBits = (($entropyBits - 128) / 32) + 4;
        $wordsCount = ($entropyBits + $checksumBits) / 11;
        return (new self($wordsCount))
            ->useEntropy($entropy)
            ->wordlist(WordList::English())
            ->mnemonic();
    }

    /**
     * @param int $wordCount
     * @return Mnemonic
     * @throws MnemonicException
     * @throws WordListException
     */
    public static function Generate($wordCount = 12){
        return (new self($wordCount))
            ->generateSecureEntropy()
            ->wordlist(WordList::English())
            ->mnemonic();
    }

    /**
     * @param $words
     * @param WordList|null $wordList
     * @param bool $verifyChecksum
     * @return Mnemonic
     * @throws MnemonicException
     * @throws WordListException
     */
    public static function Words($words,$wordList = null,$verifyChecksum = true){
        if (is_string($words)) {
            $words = explode(" ", $words);
        }

        if (!is_array($words)) {
            throw new MnemonicException('Mnemonic constructor requires an Array of words');
        }

        $wordCount = count($words);
        return (new self($wordCount))
            ->wordlist(isset($wordList) ? $wordList : WordList::English())
            ->reverse($words, $verifyChecksum);
    }

    /**
     * BIP39 constructor.
     * @param int $wordCount
     * @throws MnemonicException
     */
    public function __construct($wordCount = 12)
    {
        if ($wordCount < 12 || $wordCount > 24) {
            throw new MnemonicException('Mnemonic words count must be between 12-24');
        } elseif ($wordCount % 3 !== 0) {
            throw new MnemonicException('Words count must be generated in multiples of 3');
        }

        // Actual words count
        $this->wordsCount = $wordCount;
        // Overall entropy bits (ENT+CS)
        $this->overallBits = $this->wordsCount * 11;
        // Checksum Bits are 1 bit per 3 words, starting from 12 words with 4 CS bits
        $this->checksumBits = (($this->wordsCount - 12) / 3) + 4;
        // Entropy Bits (ENT)
        $this->entropyBits = $this->overallBits - $this->checksumBits;
    }

    /**
     * @param string $entropy
     * @return BIP39
     * @throws MnemonicException
     */
    public function useEntropy($entropy){
        self::validateEntropy($entropy);
        $this->entropy = $entropy;
        $checksum = $this->checksum($entropy, $this->checksumBits);
        $this->rawBinaryChunks = str_split($this->hex2bits($this->entropy) . $checksum, 11);
        return $this;
    }

    /**
     * @return BIP39
     * @throws MnemonicException
     * @throws \Exception
     */
    public function generateSecureEntropy(){
        if(function_exists('random_bytes')){
            $this->useEntropy(bin2hex(random_bytes($this->entropyBits / 8)));
        }else if(function_exists('openssl_random_pseudo_bytes')){
            $this->useEntropy(bin2hex(openssl_random_pseudo_bytes($this->entropyBits / 8)));
        }else{
            throw new MnemonicException('openssl_random_pseudo_bytes is no found');
        }
        
        return $this;
    }

    /**
     * @return Mnemonic
     * @throws MnemonicException
     */
    public function mnemonic(){
        if (!$this->entropy) {
            throw new MnemonicException('Entropy is not defined');
        }

        if (!$this->wordList) {
            throw new MnemonicException('Word list is not defined');
        }

        $mnemonic = new Mnemonic($this->entropy);
        foreach ($this->rawBinaryChunks as $bit) {
            $index = bindec($bit);
            $mnemonic->wordsIndex[] = $index;
            $mnemonic->words[] = $this->wordList->getWord($index);
            $mnemonic->rawBinaryChunks[] = $bit;
            $mnemonic->wordsCount++;
        }

        return $mnemonic;
    }

    /**
     * @param WordList $wordList
     * @return BIP39
     */
    public function wordList($wordList){
        $this->wordList = $wordList;
        return $this;
    }

    /**
     * @param array $words
     * @param bool $verifyChecksum
     * @return Mnemonic
     * @throws MnemonicException
     * @throws WordListException
     */
    public function reverse($words, $verifyChecksum = true){
        if (!$this->wordList) {
            throw new MnemonicException('Wordlist is not defined');
        }

        $mnemonic = new Mnemonic();
        $pos = 0;
        foreach ($words as $word) {
            $pos++;
            $index = $this->wordList->findIndex($word);
            if (is_null($index)) {
                throw new WordListException(sprintf('Invalid/unknown word at position %d', $pos));
            }

            $mnemonic->words[] = $word;
            $mnemonic->wordsIndex[] = $index;
            $mnemonic->wordsCount++;
            $mnemonic->rawBinaryChunks[] = str_pad(decbin($index), 11, '0', STR_PAD_LEFT);
        }

        $rawBinary = implode('', $mnemonic->rawBinaryChunks);
        $entropyBits = substr($rawBinary, 0, $this->entropyBits);
        $checksumBits = substr($rawBinary, $this->entropyBits, $this->checksumBits);

        $mnemonic->entropy = $this->bits2hex($entropyBits);

        // Verify Checksum?
        if ($verifyChecksum) {
            if (!hash_equals($checksumBits, $this->checksum($mnemonic->entropy, $this->checksumBits))) {
                throw new MnemonicException('Entropy checksum match failed');
            }
        }

        return $mnemonic;
    }

    /**
     * @param string $hex
     * @return string
     */
    private function hex2bits($hex){
        $bits = "";
        for ($i = 0; $i < strlen($hex); $i++) {
            $bits .= str_pad(base_convert($hex[$i], 16, 2), 4, '0', STR_PAD_LEFT);
        }
        return $bits;
    }

    /**
     * @param string $bits
     * @return string
     */
    private function bits2hex($bits){
        $hex = "";
        foreach (str_split($bits, 4) as $chunk) {
            $hex .= base_convert($chunk, 2, 16);
        }

        return $hex;
    }

    /**
     * @param string $entropy
     * @param int $bits
     * @return string
     */
    private function checksum($entropy,$bits){
        $checksumChar = ord(hash("sha256", hex2bin($entropy), true)[0]);
        $checksum = '';
        for ($i = 0; $i < $bits; $i++) {
            $checksum .= $checksumChar >> (7 - $i) & 1;
        }

        return $checksum;
    }

    /**
     * @param string $entropy
     * @throws MnemonicException
     */
    private static function validateEntropy($entropy){
        if (!preg_match('/^[a-f0-9]{2,}$/', $entropy)) {
            throw new MnemonicException('Invalid entropy (requires hexadecimal)');
        }

        $entropyBits = strlen($entropy) * 4;
        if (!in_array($entropyBits, [128, 160, 192, 224, 256])) {
            throw new MnemonicException('Invalid entropy length');
        }
    }
}
