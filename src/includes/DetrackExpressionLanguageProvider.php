<?php

namespace Detrack\DetrackWoocommerce;

use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;

class DetrackExpressionLanguageProvider implements ExpressionFunctionProviderInterface
{
    public function getFunctions()
    {
        return array_filter([
            new ExpressionFunction('carbon', function ($str) {
                return sprintf('new \Carbon\Carbon(preg_replace(\'/[^\w\d-\/ ]/\',\' \',%1$s))', $str);
            }, function ($arguments, $str) {
                return new \Carbon\Carbon(preg_replace('/[^\w\d-\/ ]/', ' ', $str));
            }),
            ExpressionFunction::fromPhp('json_encode'),
            ExpressionFunction::fromPhp('json_decode'),
            //php string functions
            function_exists('addcslashes') ? ExpressionFunction::fromPhp('addcslashes') : null,
            function_exists('addslashes') ? ExpressionFunction::fromPhp('addslashes') : null,
            function_exists('bin2hex') ? ExpressionFunction::fromPhp('bin2hex') : null,
            function_exists('chop') ? ExpressionFunction::fromPhp('chop') : null,
            function_exists('chr') ? ExpressionFunction::fromPhp('chr') : null,
            function_exists('chunk_split') ? ExpressionFunction::fromPhp('chunk_split') : null,
            function_exists('convert_cyr_string') ? ExpressionFunction::fromPhp('convert_cyr_string') : null,
            function_exists('convert_uudecode') ? ExpressionFunction::fromPhp('convert_uudecode') : null,
            function_exists('convert_uuencode') ? ExpressionFunction::fromPhp('convert_uuencode') : null,
            function_exists('count_chars') ? ExpressionFunction::fromPhp('count_chars') : null,
            function_exists('crc32') ? ExpressionFunction::fromPhp('crc32') : null,
            function_exists('crypt') ? ExpressionFunction::fromPhp('crypt') : null,
            function_exists('echo') ? ExpressionFunction::fromPhp('echo') : null,
            function_exists('explode') ? ExpressionFunction::fromPhp('explode') : null,
            function_exists('fprintf') ? ExpressionFunction::fromPhp('fprintf') : null,
            function_exists('get_html_translation_table') ? ExpressionFunction::fromPhp('get_html_translation_table') : null,
            function_exists('hebrev') ? ExpressionFunction::fromPhp('hebrev') : null,
            function_exists('hebrevc') ? ExpressionFunction::fromPhp('hebrevc') : null,
            function_exists('hex2bin') ? ExpressionFunction::fromPhp('hex2bin') : null,
            function_exists('html_entity_decode') ? ExpressionFunction::fromPhp('html_entity_decode') : null,
            function_exists('htmlentities') ? ExpressionFunction::fromPhp('htmlentities') : null,
            function_exists('htmlspecialchars_decode') ? ExpressionFunction::fromPhp('htmlspecialchars_decode') : null,
            function_exists('htmlspecialchars') ? ExpressionFunction::fromPhp('htmlspecialchars') : null,
            function_exists('implode') ? ExpressionFunction::fromPhp('implode') : null,
            function_exists('join') ? ExpressionFunction::fromPhp('join') : null,
            function_exists('lcfirst') ? ExpressionFunction::fromPhp('lcfirst') : null,
            function_exists('levenshtein') ? ExpressionFunction::fromPhp('levenshtein') : null,
            function_exists('localeconv') ? ExpressionFunction::fromPhp('localeconv') : null,
            function_exists('ltrim') ? ExpressionFunction::fromPhp('ltrim') : null,
            function_exists('md5_file') ? ExpressionFunction::fromPhp('md5_file') : null,
            function_exists('md5') ? ExpressionFunction::fromPhp('md5') : null,
            function_exists('metaphone') ? ExpressionFunction::fromPhp('metaphone') : null,
            function_exists('money_format') ? ExpressionFunction::fromPhp('money_format') : null,
            function_exists('nl_langinfo') ? ExpressionFunction::fromPhp('nl_langinfo') : null,
            function_exists('nl2br') ? ExpressionFunction::fromPhp('nl2br') : null,
            function_exists('number_format') ? ExpressionFunction::fromPhp('number_format') : null,
            function_exists('ord') ? ExpressionFunction::fromPhp('ord') : null,
            function_exists('parse_str') ? ExpressionFunction::fromPhp('parse_str') : null,
            function_exists('print') ? ExpressionFunction::fromPhp('print') : null,
            function_exists('printf') ? ExpressionFunction::fromPhp('printf') : null,
            function_exists('quoted_printable_decode') ? ExpressionFunction::fromPhp('quoted_printable_decode') : null,
            function_exists('quoted_printable_encode') ? ExpressionFunction::fromPhp('quoted_printable_encode') : null,
            function_exists('quotemeta') ? ExpressionFunction::fromPhp('quotemeta') : null,
            function_exists('rtrim') ? ExpressionFunction::fromPhp('rtrim') : null,
            function_exists('setlocale') ? ExpressionFunction::fromPhp('setlocale') : null,
            function_exists('sha1_file') ? ExpressionFunction::fromPhp('sha1_file') : null,
            function_exists('sha1') ? ExpressionFunction::fromPhp('sha1') : null,
            function_exists('similar_text') ? ExpressionFunction::fromPhp('similar_text') : null,
            function_exists('soundex') ? ExpressionFunction::fromPhp('soundex') : null,
            function_exists('sprintf') ? ExpressionFunction::fromPhp('sprintf') : null,
            function_exists('sscanf') ? ExpressionFunction::fromPhp('sscanf') : null,
            function_exists('str_getcsv') ? ExpressionFunction::fromPhp('str_getcsv') : null,
            function_exists('str_ireplace') ? ExpressionFunction::fromPhp('str_ireplace') : null,
            function_exists('str_pad') ? ExpressionFunction::fromPhp('str_pad') : null,
            function_exists('str_repeat') ? ExpressionFunction::fromPhp('str_repeat') : null,
            function_exists('str_replace') ? ExpressionFunction::fromPhp('str_replace') : null,
            function_exists('str_rot13') ? ExpressionFunction::fromPhp('str_rot13') : null,
            function_exists('str_shuffle') ? ExpressionFunction::fromPhp('str_shuffle') : null,
            function_exists('str_split') ? ExpressionFunction::fromPhp('str_split') : null,
            function_exists('str_word_count') ? ExpressionFunction::fromPhp('str_word_count') : null,
            function_exists('strcasecmp') ? ExpressionFunction::fromPhp('strcasecmp') : null,
            function_exists('strchr') ? ExpressionFunction::fromPhp('strchr') : null,
            function_exists('strcmp') ? ExpressionFunction::fromPhp('strcmp') : null,
            function_exists('strcoll') ? ExpressionFunction::fromPhp('strcoll') : null,
            function_exists('strcspn') ? ExpressionFunction::fromPhp('strcspn') : null,
            function_exists('strip_tags') ? ExpressionFunction::fromPhp('strip_tags') : null,
            function_exists('stripcslashes') ? ExpressionFunction::fromPhp('stripcslashes') : null,
            function_exists('stripos') ? ExpressionFunction::fromPhp('stripos') : null,
            function_exists('stripslashes') ? ExpressionFunction::fromPhp('stripslashes') : null,
            function_exists('stristr') ? ExpressionFunction::fromPhp('stristr') : null,
            function_exists('strlen') ? ExpressionFunction::fromPhp('strlen') : null,
            function_exists('strnatcasecmp') ? ExpressionFunction::fromPhp('strnatcasecmp') : null,
            function_exists('strnatcmp') ? ExpressionFunction::fromPhp('strnatcmp') : null,
            function_exists('strncasecmp') ? ExpressionFunction::fromPhp('strncasecmp') : null,
            function_exists('strncmp') ? ExpressionFunction::fromPhp('strncmp') : null,
            function_exists('strpbrk') ? ExpressionFunction::fromPhp('strpbrk') : null,
            function_exists('strpos') ? ExpressionFunction::fromPhp('strpos') : null,
            function_exists('strrchr') ? ExpressionFunction::fromPhp('strrchr') : null,
            function_exists('strrev') ? ExpressionFunction::fromPhp('strrev') : null,
            function_exists('strripos') ? ExpressionFunction::fromPhp('strripos') : null,
            function_exists('strrpos') ? ExpressionFunction::fromPhp('strrpos') : null,
            function_exists('strspn') ? ExpressionFunction::fromPhp('strspn') : null,
            function_exists('strstr') ? ExpressionFunction::fromPhp('strstr') : null,
            function_exists('strtok') ? ExpressionFunction::fromPhp('strtok') : null,
            function_exists('strtolower') ? ExpressionFunction::fromPhp('strtolower') : null,
            function_exists('strtoupper') ? ExpressionFunction::fromPhp('strtoupper') : null,
            function_exists('strtr') ? ExpressionFunction::fromPhp('strtr') : null,
            function_exists('substr_compare') ? ExpressionFunction::fromPhp('substr_compare') : null,
            function_exists('substr_count') ? ExpressionFunction::fromPhp('substr_count') : null,
            function_exists('substr_replace') ? ExpressionFunction::fromPhp('substr_replace') : null,
            function_exists('substr') ? ExpressionFunction::fromPhp('substr') : null,
            function_exists('trim') ? ExpressionFunction::fromPhp('trim') : null,
            function_exists('ucfirst') ? ExpressionFunction::fromPhp('ucfirst') : null,
            function_exists('ucwords') ? ExpressionFunction::fromPhp('ucwords') : null,
            function_exists('vfprintf') ? ExpressionFunction::fromPhp('vfprintf') : null,
            function_exists('vprintf') ? ExpressionFunction::fromPhp('vprintf') : null,
            function_exists('vsprintf') ? ExpressionFunction::fromPhp('vsprintf') : null,
            function_exists('wordwrap') ? ExpressionFunction::fromPhp('wordwrap') : null,
            //php array functions
            function_exists('array_change_key_case') ? ExpressionFunction::fromPhp('array_change_key_case') : null,
            function_exists('array_chunk') ? ExpressionFunction::fromPhp('array_chunk') : null,
            function_exists('array_column') ? ExpressionFunction::fromPhp('array_column') : null,
            function_exists('array_combine') ? ExpressionFunction::fromPhp('array_combine') : null,
            function_exists('array_count_values') ? ExpressionFunction::fromPhp('array_count_values') : null,
            function_exists('array_diff_assoc') ? ExpressionFunction::fromPhp('array_diff_assoc') : null,
            function_exists('array_diff_key') ? ExpressionFunction::fromPhp('array_diff_key') : null,
            function_exists('array_diff_uassoc') ? ExpressionFunction::fromPhp('array_diff_uassoc') : null,
            function_exists('array_diff_ukey') ? ExpressionFunction::fromPhp('array_diff_ukey') : null,
            function_exists('array_diff') ? ExpressionFunction::fromPhp('array_diff') : null,
            function_exists('array_fill_keys') ? ExpressionFunction::fromPhp('array_fill_keys') : null,
            function_exists('array_fill') ? ExpressionFunction::fromPhp('array_fill') : null,
            function_exists('array_filter') ? ExpressionFunction::fromPhp('array_filter') : null,
            function_exists('array_flip') ? ExpressionFunction::fromPhp('array_flip') : null,
            function_exists('array_intersect_assoc') ? ExpressionFunction::fromPhp('array_intersect_assoc') : null,
            function_exists('array_intersect_key') ? ExpressionFunction::fromPhp('array_intersect_key') : null,
            function_exists('array_intersect_uassoc') ? ExpressionFunction::fromPhp('array_intersect_uassoc') : null,
            function_exists('array_intersect_ukey') ? ExpressionFunction::fromPhp('array_intersect_ukey') : null,
            function_exists('array_intersect') ? ExpressionFunction::fromPhp('array_intersect') : null,
            function_exists('array_key_exists') ? ExpressionFunction::fromPhp('array_key_exists') : null,
            function_exists('array_key_first') ? ExpressionFunction::fromPhp('array_key_first') : null,
            function_exists('array_key_last') ? ExpressionFunction::fromPhp('array_key_last') : null,
            function_exists('array_keys') ? ExpressionFunction::fromPhp('array_keys') : null,
            function_exists('array_map') ? ExpressionFunction::fromPhp('array_map') : null,
            function_exists('array_merge_recursive') ? ExpressionFunction::fromPhp('array_merge_recursive') : null,
            function_exists('array_merge') ? ExpressionFunction::fromPhp('array_merge') : null,
            function_exists('array_multisort') ? ExpressionFunction::fromPhp('array_multisort') : null,
            function_exists('array_pad') ? ExpressionFunction::fromPhp('array_pad') : null,
            function_exists('array_pop') ? ExpressionFunction::fromPhp('array_pop') : null,
            function_exists('array_product') ? ExpressionFunction::fromPhp('array_product') : null,
            function_exists('array_push') ? ExpressionFunction::fromPhp('array_push') : null,
            function_exists('array_rand') ? ExpressionFunction::fromPhp('array_rand') : null,
            function_exists('array_reduce') ? ExpressionFunction::fromPhp('array_reduce') : null,
            function_exists('array_replace_recursive') ? ExpressionFunction::fromPhp('array_replace_recursive') : null,
            function_exists('array_replace') ? ExpressionFunction::fromPhp('array_replace') : null,
            function_exists('array_reverse') ? ExpressionFunction::fromPhp('array_reverse') : null,
            function_exists('array_search') ? ExpressionFunction::fromPhp('array_search') : null,
            function_exists('array_shift') ? ExpressionFunction::fromPhp('array_shift') : null,
            function_exists('array_slice') ? ExpressionFunction::fromPhp('array_slice') : null,
            function_exists('array_splice') ? ExpressionFunction::fromPhp('array_splice') : null,
            function_exists('array_sum') ? ExpressionFunction::fromPhp('array_sum') : null,
            function_exists('array_udiff_assoc') ? ExpressionFunction::fromPhp('array_udiff_assoc') : null,
            function_exists('array_udiff_uassoc') ? ExpressionFunction::fromPhp('array_udiff_uassoc') : null,
            function_exists('array_udiff') ? ExpressionFunction::fromPhp('array_udiff') : null,
            function_exists('array_uintersect_assoc') ? ExpressionFunction::fromPhp('array_uintersect_assoc') : null,
            function_exists('array_uintersect_uassoc') ? ExpressionFunction::fromPhp('array_uintersect_uassoc') : null,
            function_exists('array_uintersect') ? ExpressionFunction::fromPhp('array_uintersect') : null,
            function_exists('array_unique') ? ExpressionFunction::fromPhp('array_unique') : null,
            function_exists('array_unshift') ? ExpressionFunction::fromPhp('array_unshift') : null,
            function_exists('array_values') ? ExpressionFunction::fromPhp('array_values') : null,
            function_exists('array_walk_recursive') ? ExpressionFunction::fromPhp('array_walk_recursive') : null,
            function_exists('array_walk') ? ExpressionFunction::fromPhp('array_walk') : null,
            function_exists('array') ? ExpressionFunction::fromPhp('array') : null,
            function_exists('arsort') ? ExpressionFunction::fromPhp('arsort') : null,
            function_exists('asort') ? ExpressionFunction::fromPhp('asort') : null,
            function_exists('compact') ? ExpressionFunction::fromPhp('compact') : null,
            function_exists('count') ? ExpressionFunction::fromPhp('count') : null,
            function_exists('current') ? ExpressionFunction::fromPhp('current') : null,
            function_exists('each') ? ExpressionFunction::fromPhp('each') : null,
            function_exists('end') ? ExpressionFunction::fromPhp('end') : null,
            function_exists('extract') ? ExpressionFunction::fromPhp('extract') : null,
            function_exists('in_array') ? ExpressionFunction::fromPhp('in_array') : null,
            function_exists('key_exists') ? ExpressionFunction::fromPhp('key_exists') : null,
            function_exists('key') ? ExpressionFunction::fromPhp('key') : null,
            function_exists('krsort') ? ExpressionFunction::fromPhp('krsort') : null,
            function_exists('ksort') ? ExpressionFunction::fromPhp('ksort') : null,
            function_exists('list') ? ExpressionFunction::fromPhp('list') : null,
            function_exists('natcasesort') ? ExpressionFunction::fromPhp('natcasesort') : null,
            function_exists('natsort') ? ExpressionFunction::fromPhp('natsort') : null,
            function_exists('next') ? ExpressionFunction::fromPhp('next') : null,
            function_exists('pos') ? ExpressionFunction::fromPhp('pos') : null,
            function_exists('prev') ? ExpressionFunction::fromPhp('prev') : null,
            function_exists('range') ? ExpressionFunction::fromPhp('range') : null,
            function_exists('reset') ? ExpressionFunction::fromPhp('reset') : null,
            function_exists('rsort') ? ExpressionFunction::fromPhp('rsort') : null,
            function_exists('shuffle') ? ExpressionFunction::fromPhp('shuffle') : null,
            function_exists('sizeof') ? ExpressionFunction::fromPhp('sizeof') : null,
            function_exists('sort') ? ExpressionFunction::fromPhp('sort') : null,
            function_exists('uasort') ? ExpressionFunction::fromPhp('uasort') : null,
            function_exists('uksort') ? ExpressionFunction::fromPhp('uksort') : null,
            function_exists('usort') ? ExpressionFunction::fromPhp('usort') : null,
        ]);
    }
}
