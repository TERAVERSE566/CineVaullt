<?php
require_once 'api/db_connect.php';

$movies = [
 ['Kalki 2898 AD','2024','3h 1m','7.6','Action · Sci-Fi','A modern-day avatar of Vishnu descends to Earth to protect the world.','https://image.tmdb.org/t/p/w500/mXpXJ9Q4I6zBndZqV4U7J8T8z9R.jpg','xmPaHFpOCow','movie','bollywood'],
 ['Animal','2023','3h 24m','6.5','Action · Crime','A son undergoes a remarkable transformation to protect his father.','https://image.tmdb.org/t/p/w500/1XddvJ7N45qGgV72M38KzKjGZ8H.jpg','oEBFJEVFbP4','movie','bollywood'],
 ['Tumbbad','2018','1h 44m','8.2','Horror · Fantasy','A mythological story about a goddess who created the entire universe.','https://image.tmdb.org/t/p/w500/qD7OqQ4T3A3lqQ8q3w8xV0q0z3S.jpg','xKOFB_sCNgA','movie','bollywood'],
 ['Bhag Milkha Bhag','2013','3h 8m','8.2','Drama · Sport','The truth behind the ascension of Milkha Singh.','https://image.tmdb.org/t/p/w500/8gVwR2L4zS0L0wV9z2R1tL2y2S0.jpg','OuMBIBLCM60','movie','bollywood'],
 ['Sholay','1975','3h 24m','8.1','Action · Adventure','Two ex-convicts are hired to capture a ruthless dacoit.','https://image.tmdb.org/t/p/w500/yQ0wV1X4K8oQ0F0vQ3xV9J9K8K3.jpg','kK4yHCNFqhY','movie','bollywood'],
 ['Phir Hera Pheri','2006','2h 33m','7.2','Comedy','Baburao, Raju and Shyam are living happily after having risen to wealth.','https://image.tmdb.org/t/p/w500/wP1Nf9R8U2o5V4R5T9bY8F8G3c.jpg','aDxfqKVSars','movie','bollywood'],
 ['Happy New Year','2014','3h 0m','5.0','Action · Comedy','A team of losers plan to pull off a massive diamond heist.','https://image.tmdb.org/t/p/w500/8Z0M2P2mG1T4g1V6A3s2x4T8A3H.jpg','b_BI3FEPOVE','movie','bollywood'],
 ['Andhadhun','2018','2h 19m','8.2','Crime · Thriller','A series of mysterious events changes the life of a blind pianist.','https://image.tmdb.org/t/p/w500/2i1A1z8J2k0V8A0E4J0W1F0s5Q8.jpg','YFGM8HYyOlE','movie','bollywood'],
 ['Gangs of Wasseypur','2012','5h 21m','8.2','Action · Crime','A clash between Sultan and Shahid Khan leads to the expulsion of Khan.','https://image.tmdb.org/t/p/w500/2L2H2C3m8Q9Q2A0D4Q4V5K3K8A7.jpg','mIBJ5FqbYIQ','movie','bollywood'],
 ['Mission: Impossible - Fallout','2018','2h 27m','7.7','Action · Adventure','Ethan Hunt and his IMF team, along with some familiar allies, race against time after a mission goes wrong.','https://image.tmdb.org/t/p/w500/80PWnSTkygi3QWWmJ3hrAwqvLnO.jpg','avz06PDqgOE','movie','action'],
 ['Nobody','2021','1h 32m','7.4','Action · Crime','A bystander who intervenes to help a woman being harassed by a group of men becomes the target of a vengeful drug lord.','https://image.tmdb.org/t/p/w500/q20e2x3L1x7a3l9hR6qY6B9D6T1.jpg','pM_IiN7MgDs','movie','action'],
 ['Extraction 2','2023','2h 2m','7.0','Action · Thriller','Tyler Rake is back as the Australian black ops mercenary, tasked with another deadly mission.','https://image.tmdb.org/t/p/w500/jL5Q0n7n4x0q3n5z4V8R9X2B4F0.jpg','73IyOlsGGgY','movie','action'],
 ['Gladiator II','2024','2h 30m','0.0','Action · Adventure','Follows the story of Lucius, the nephew of Commodus.','https://image.tmdb.org/t/p/w500/8c4a8kE7PzA7mL41V0R1l8K3z0s.jpg','t8jIcBFnHOA','movie','trending'],
 ['Tron: Ares','2025','2h 0m','0.0','Sci-Fi · Action','Ares, a highly sophisticated Program, is sent from the digital world into the real world on a dangerous mission.','https://image.tmdb.org/t/p/w500/3o1R8L8Q9J8X5F5E4z0X6n2E0v.jpg','lP6MBl5mMSa','movie','trending'],
 ['Fighter','2024','2h 46m','6.8','Action · Thriller','Top IAF aviators come together in the face of imminent danger.','https://image.tmdb.org/t/p/w500/1XddvJ7N45qGgV72M38KzKjGZ8H.jpg','D-YF2QSOSB4','movie','bollywood'],
 ['Jawan','2023','2h 49m','7.1','Action · Thriller','A man is driven by a personal vendetta to rectify the wrongs in society.','https://image.tmdb.org/t/p/w500/n5X6vN3hH9L2Z8N2R5P6P8Q1W0.jpg','TJ4mFnWBkEs','movie','bollywood']
];

$stmt = $pdo->prepare("INSERT IGNORE INTO content (title,release_year,duration,rating,genre,description,poster_url,trailer_url,content_type,category) VALUES (?,?,?,?,?,?,?,?,?,?)");
$updateStmt = $pdo->prepare("UPDATE content SET trailer_url = ? WHERE title = ?");
$count = 0;
foreach ($movies as $m) {
    try {
        // Check if exists
        $chk = $pdo->prepare("SELECT id FROM content WHERE title=?");
        $chk->execute([$m[0]]);
        if ($chk->fetch()) {
            $updateStmt->execute([$m[7], $m[0]]);
            continue;
        }
        $stmt->execute($m);
        $count++;
    } catch(Exception $e) { /* skip */ }
}

// Add trailer_urls to previous seed data based on the old script.js TRAILERS list
$trailers = [
  'War' => 'KKFanta3JJ4',
  'Pathaan' => 'vqu4z34wENw',
  'KGF: Chapter 2' => 'YIE4TrjWIZI',
  'RRR' => 'f_vbAtFSEc0',
  'Baahubali 2: The Conclusion' => 'WNel_N6dp20',
  'Uri: The Surgical Strike' => '0R-I3mI4f1I',
  'Pushpa: The Rise' => 'Q1NKMPhP8PY',
  '3 Idiots' => 'xvszmNXdM4w',
  'Dangal' => 'x_7YlGv9u1g',
  'Dilwale Dulhania Le Jayenge' => 'csFHVqLMhcA',
  'John Wick' => 'qEVUtrk8_B4',
  'Top Gun: Maverick' => 'giXco2jaZ_4',
  'The Dark Knight' => 'EXeTwQWrcwY',
  'Mad Max: Fury Road' => 'hEJnMQG9ev8',
  'Avengers: Endgame' => 'TcMBFSGVi1c',
  'The Shawshank Redemption' => '6hB3S9bIaco',
  'The Godfather' => 'sY1S34973zA',
  'Inception' => 'YoHD9XEInc0',
  'Interstellar' => 'zSWdZVtXT7E',
  'Oppenheimer' => 'uYPbbksJxIg',
  'Breaking Bad' => 'HhesaQXLuRY',
  'Stranger Things' => 'b9EkMc79ZSU',
  'Game of Thrones' => 'KPLWWIOCOOQ',
  'Money Heist' => 'hMANIarjTbc'
];

foreach ($trailers as $title => $url) {
    $updateStmt->execute([$url, $title]);
}

echo "Added $count new Indian/Pan-World titles, and updated trailer URLs!\n";
