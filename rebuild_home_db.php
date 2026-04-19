<?php
// Extract the Hero banner JS + header part from the current home.php
$homeContent = file_get_contents('c:/xampp/htdocs/moviz/home.php');

// We want to keep everything from <?php down to the end of <section class="hero" id="heroBanner">
preg_match('/(<\?php\s*session_start\(\);.*?<\/section>)/s', $homeContent, $matches);
$topSection = $matches[1] ?? '';

// If we can't find it, we'll manually rebuild the top part
if (!$topSection) {
    die("Could not extract top section");
}

$newHome = $topSection . "\n\n";
$newHome .= "<?php\n";
$newHome .= "require_once 'api/db_connect.php';\n\n";

$newHome .= "// Fetch dynamic sections\n";
$newHome .= "\$sections = [\n";
$newHome .= "    'Continue Watching' => 'SELECT * FROM content ORDER BY RAND() LIMIT 6',\n";
$newHome .= "    'Trending Now' => 'SELECT * FROM content WHERE rating > 8.0 ORDER BY rating DESC LIMIT 8',\n";
$newHome .= "    'Action Blockbusters' => 'SELECT * FROM content WHERE genre LIKE \"%Action%\" LIMIT 8',\n";
$newHome .= "    'Sci-Fi & Fantasy' => 'SELECT * FROM content WHERE genre LIKE \"%Sci-Fi%\" OR genre LIKE \"%Fantasy%\" LIMIT 8',\n";
$newHome .= "    'Binge-Worthy Series' => 'SELECT * FROM content WHERE content_type = \"series\" ORDER BY id DESC LIMIT 8',\n";
$newHome .= "];\n\n";

$newHome .= "foreach (\$sections as \$title => \$query) {\n";
$newHome .= "    \$stmt = \$pdo->query(\$query);\n";
$newHome .= "    \$results = \$stmt->fetchAll();\n";
$newHome .= "    if(count(\$results) > 0) {\n";
$newHome .= "        echo '<section class=\"content-section\">';\n";
$newHome .= "        echo '    <div class=\"section-header\">';\n";
$newHome .= "        echo '        <h2 class=\"section-title\">' . htmlspecialchars(\$title) . '</h2>';\n";
$newHome .= "        echo '    </div>';\n";
$newHome .= "        echo '    <div class=\"row-scroll\">';\n";
$newHome .= "        foreach (\$results as \$row) {\n";
$newHome .= "            \$progressHtml = (\$title === 'Continue Watching') ? '<div class=\"progress-bar-bg\"><div class=\"progress-bar-fill\" style=\"width:'.rand(15,85).'%\"></div></div>' : '';\n";
$newHome .= "            \$escapedTitle = htmlspecialchars(addslashes(\$row['title']));\n";
$newHome .= "            \$escapedYear = htmlspecialchars(addslashes(\$row['release_year']));\n";
$newHome .= "            \$escapedDur = htmlspecialchars(addslashes(\$row['duration']));\n";
$newHome .= "            \$escapedRatings = htmlspecialchars(addslashes(\$row['rating']));\n";
$newHome .= "            \$escapedGenre = htmlspecialchars(addslashes(\$row['genre']));\n";
$newHome .= "            \$escapedDesc = htmlspecialchars(addslashes(\$row['description']));\n";
$newHome .= "            \$escapedPoster = htmlspecialchars(addslashes(\$row['poster_url']));\n";
$newHome .= "            echo '        <div class=\"movie-card\" onclick=\"window.location.href=\'watch.php?id='.(int)\$row['id'].'\'\">';\n";
$newHome .= "            echo '            <div class=\"card-img-wrap\">';\n";
$newHome .= "            echo '                <img src=\"'.htmlspecialchars(\$row['poster_url']).'\" loading=\"lazy\" alt=\"'.htmlspecialchars(\$row['title']).'\">';\n";
$newHome .= "            echo '                <div class=\"card-overlay\"><div class=\"card-play-btn\"></div></div>';\n";
$newHome .= "            echo \$progressHtml;\n";
$newHome .= "            echo '            </div>';\n";
$newHome .= "            echo '            <div class=\"card-info\">';\n";
$newHome .= "            echo '                <span class=\"card-title\">'.htmlspecialchars(\$row['title']).'</span>';\n";
$newHome .= "            echo '            </div>';\n";
$newHome .= "        echo '        </div>';\n";
$newHome .= "        }\n";
$newHome .= "        echo '    </div>';\n";
$newHome .= "        echo '</section>';\n";
$newHome .= "    }\n";
$newHome .= "}\n";
$newHome .= "?>\n\n";

// Add pricing options and footer exactly as before
$newHome .= "<!-- ═══════════════════════════════════════════════════════\n";
$newHome .= "     PREMIUM MEMBERSHIP\n";
$newHome .= "     ═══════════════════════════════════════════════════════ -->\n";
$newHome .= "<section class=\"pricing-section\">\n";
$newHome .= "    <div class=\"section-header\" style=\"justify-content:center\">\n";
$newHome .= "        <h2 class=\"section-title\"> Upgrade to CineVault+</h2>\n";
$newHome .= "    </div>\n";
$newHome .= "    <div class=\"pricing-grid\">\n";
$newHome .= "        <div class=\"price-card\">\n";
$newHome .= "            <div class=\"price-title\">Basic</div>\n";
$newHome .= "            <div class=\"price-amount\">$0<span>/mo</span></div>\n";
$newHome .= "            <ul class=\"price-features\">\n";
$newHome .= "                <li>Access 100+ Free Titles</li>\n";
$newHome .= "                <li>720p HD Streaming</li>\n";
$newHome .= "                <li>Ad-Supported Environment</li>\n";
$newHome .= "            </ul>\n";
$newHome .= "            <button class=\"glow-btn\" style=\"background:#333;box-shadow:none;\">Current Plan</button>\n";
$newHome .= "        </div>\n";
$newHome .= "        <div class=\"price-card premium\">\n";
$newHome .= "            <div class=\"price-badge\">POPULAR</div>\n";
$newHome .= "            <div class=\"price-title\">Pro</div>\n";
$newHome .= "            <div class=\"price-amount\">$9<span>/mo</span></div>\n";
$newHome .= "            <ul class=\"price-features\">\n";
$newHome .= "                <li>Access Entire Vault</li>\n";
$newHome .= "                <li>1080p Ultra HD Streaming</li>\n";
$newHome .= "                <li>Ad-Free Environment</li>\n";
$newHome .= "                <li>Watch Party Privileges</li>\n";
$newHome .= "            </ul>\n";
$newHome .= "            <button class=\"glow-btn\">Upgrade Now</button>\n";
$newHome .= "        </div>\n";
$newHome .= "        <div class=\"price-card\">\n";
$newHome .= "            <div class=\"price-title\">Family Max</div>\n";
$newHome .= "            <div class=\"price-amount\">$14<span>/mo</span></div>\n";
$newHome .= "            <ul class=\"price-features\">\n";
$newHome .= "                <li>Access Entire Vault</li>\n";
$newHome .= "                <li>4K HDR10+ Quality</li>\n";
$newHome .= "                <li>Offline Downloads</li>\n";
$newHome .= "                <li>5 Concurrent Screens</li>\n";
$newHome .= "            </ul>\n";
$newHome .= "            <button class=\"glow-btn\" style=\"background:#555;box-shadow:none;\">Choose Max</button>\n";
$newHome .= "        </div>\n";
$newHome .= "    </div>\n";
$newHome .= "</section>\n\n";

$newHome .= "<?php include 'includes/footer.php'; ?>\n";

file_put_contents('c:/xampp/htdocs/moviz/home.php', $newHome);
echo "Dynamic database-driven home.php has been written successfully.\n";
