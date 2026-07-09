<?php
function searchDDGLite($query) {
    $url = "https://lite.duckduckgo.com/lite/";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['q' => $query]));
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36");
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $html = curl_exec($ch);
    curl_close($ch);
    
    if (!$html) return [];
    
    $dom = new DOMDocument();
    @$dom->loadHTML('<?xml encoding="UTF-8">' . $html);
    $xpath = new DOMXPath($dom);
    
    $results = [];
    // DDG Lite search results are in class="result-link" or inside result__snippet tables.
    // Let's extract links from the tables
    $links = $xpath->query("//a[contains(@class, 'result-link')]");
    $snippets = $xpath->query("//td[@class='result-snippet']");
    
    for ($i = 0; $i < $links->length; $i++) {
        $link = $links->item($i);
        $href = $link->getAttribute('href');
        
        // Clean proxy URLs (DDG Lite wraps external links in proxy URLs like /l/?kh=-1&uddg=HTTP_URL)
        if (str_contains($href, 'uddg=')) {
            parse_str(parse_url($href, PHP_URL_QUERY), $queryParts);
            if (isset($queryParts['uddg'])) {
                $href = $queryParts['uddg'];
            }
        }
        
        $snippetText = '';
        if ($snippets->item($i)) {
            $snippetText = trim($snippets->item($i)->textContent);
        }
        
        $results[] = [
            'title' => trim($link->textContent),
            'url' => $href,
            'snippet' => $snippetText
        ];
    }
    
    return $results;
}

$res = searchDDGLite("โรงเรียนวัดหาดทรายแก้ว");
echo "Found " . count($res) . " results:\n";
foreach (array_slice($res, 0, 5) as $r) {
    echo "Title: {$r['title']}\nURL: {$r['url']}\nSnippet: {$r['snippet']}\n\n";
}
