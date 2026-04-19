<?php
require_once 'api/db_connect.php';

$movies = [
 ['Inception','2010','2h 28m','8.8','Sci-Fi · Thriller','A thief who steals corporate secrets through dream-sharing is given the inverse task of planting an idea.','https://image.tmdb.org/t/p/w500/9gk7adHYeDvHkCSEqAvQNLV5Uge.jpg','movie','hollywood'],
 ['The Dark Knight','2008','2h 32m','9.0','Action · Crime · Drama','Batman fights Joker in a battle for Gotham\'s soul.','https://image.tmdb.org/t/p/w500/qJ2tW6WMUDux911r6m7haRef0WH.jpg','movie','hollywood'],
 ['Interstellar','2014','2h 49m','8.6','Sci-Fi · Drama','A crew of astronauts travel through a wormhole near Saturn.','https://image.tmdb.org/t/p/w500/gEU2QniE6E77NI6lCU6MxlNBvIx.jpg','movie','hollywood'],
 ['Avengers: Endgame','2019','3h 1m','8.4','Action · Sci-Fi','The Avengers assemble once more to reverse Thanos\'s actions.','https://image.tmdb.org/t/p/w500/or06FN3Dka5tukK1e9sl16pB3iy.jpg','movie','hollywood'],
 ['The Matrix','1999','2h 16m','8.7','Action · Sci-Fi','A hacker discovers reality is a simulation.','https://image.tmdb.org/t/p/w500/f89U3ADr1oiB1s9GkdPOEpXUk5H.jpg','movie','hollywood'],
 ['Pulp Fiction','1994','2h 34m','8.9','Crime · Drama','Interconnected stories of criminals in LA.','https://image.tmdb.org/t/p/w500/d5iIlFn5s0ImszYzBPb8JPIfbXD.jpg','movie','hollywood'],
 ['The Godfather','1972','2h 55m','9.2','Crime · Drama','The Corleone family mafia saga.','https://image.tmdb.org/t/p/w500/3bhkrj58Vtu7enYsLeBHka4eKPb.jpg','movie','hollywood'],
 ['Fight Club','1999','2h 19m','8.8','Drama · Thriller','An insomniac forms an underground fight club.','https://image.tmdb.org/t/p/w500/pB8BM7pdSp6B6Ih7QZ4DrQ3PmJK.jpg','movie','hollywood'],
 ['Forrest Gump','1994','2h 22m','8.8','Drama · Romance','Forrest runs through several decades of American history.','https://image.tmdb.org/t/p/w500/arw2vcBveWOVZr6pxd9XTd1TdQa.jpg','movie','hollywood'],
 ['The Shawshank Redemption','1994','2h 22m','9.3','Drama','A banker is sentenced to life in Shawshank State Prison.','https://image.tmdb.org/t/p/w500/lyQBXzOQSuE59IsHyhrp0qIiPAz.jpg','movie','top10'],
 ['Goodfellas','1990','2h 26m','8.7','Crime · Drama','The story of Henry Hill and his life in the mob.','https://image.tmdb.org/t/p/w500/aKuFiU82s5ISJpGZp7YkIr3kCUd.jpg','movie','hollywood'],
 ['Joker','2019','2h 2m','8.4','Crime · Drama','Failed comedian Arthur Fleck descends into madness.','https://image.tmdb.org/t/p/w500/udDclJoHjfjb8Ekgsd4FDteOkCU.jpg','movie','hollywood'],
 ['Spider-Man: No Way Home','2021','2h 28m','8.3','Action · Adventure','Peter Parker asks Doctor Strange for help.','https://image.tmdb.org/t/p/w500/1g0dhYtq4irTY1GPXvft6k4YLjm.jpg','movie','hollywood'],
 ['Top Gun: Maverick','2022','2h 11m','8.3','Action · Drama','Maverick must confront ghosts of the past.','https://image.tmdb.org/t/p/w500/62HCnUTziyWcpDaBO2i1DX17ljH.jpg','movie','trending'],
 ['Everything Everywhere All at Once','2022','2h 19m','7.8','Action · Comedy · Sci-Fi','A woman discovers she can access parallel universe versions.','https://image.tmdb.org/t/p/w500/w3LxiVYdWWRvEVdn5RYq6jIqkb1.jpg','movie','hollywood'],
 ['Oppenheimer','2023','3h 0m','8.5','Drama · History','The story of the American scientist who built the atomic bomb.','https://image.tmdb.org/t/p/w500/8Gxv8gSFCU0XGDykEGv7zR1n2ua.jpg','movie','trending'],
 ['Dune','2021','2h 35m','7.9','Sci-Fi · Adventure','A noble family becomes embroiled in a war over a desert planet.','https://image.tmdb.org/t/p/w500/d5NXSklpcKDU tqd0xTvBqYbPSZr.jpg','movie','hollywood'],
 ['John Wick','2014','1h 41m','7.4','Action · Thriller','Retired hitman seeks vengeance for his dead dog.','https://image.tmdb.org/t/p/w500/fZPSd91yGE9fCcCe6OoQr6E3Bev.jpg','movie','action'],
 ['Mad Max: Fury Road','2015','2h 0m','8.1','Action · Sci-Fi','In a post-apocalyptic wasteland, a woman rebels.','https://image.tmdb.org/t/p/w500/8tZYtuWezp8JbcsvHYO0O46tFbo.jpg','movie','action'],
 ['Deadpool & Wolverine','2024','2h 8m','8.1','Action · Comedy','Deadpool teams up with Wolverine for an adventure.','https://image.tmdb.org/t/p/w500/8cdWjvZQUExUUTzyp4t6EDMubfO.jpg','movie','trending'],
 ['KGF: Chapter 2','2022','2h 48m','8.4','Action · Drama','Rocky\'s rise to power continues.','https://image.tmdb.org/t/p/w500/4j0PNHkMr5ax3IA8tjtxcmPU3QT.jpg','movie','action'],
 ['RRR','2022','3h 7m','7.9','Action · Drama','Two revolutionaries join forces against colonial rule.','https://image.tmdb.org/t/p/w500/nEufeZlyAOLqO2brrs0yeF1lgXO.jpg','movie','action'],
 ['Pathaan','2023','2h 26m','5.9','Action · Thriller','An Indian spy returns from exile to battle a mercenary.','https://image.tmdb.org/t/p/w500/kDp3MFp5WBT0vOp8IDMh7GBZVAB.jpg','movie','bollywood'],
 ['3 Idiots','2009','2h 50m','8.4','Comedy · Drama','Two friends search for their long-lost companion.','https://image.tmdb.org/t/p/w500/66A9MqXZyHSBg21QB3florroV9Q.jpg','movie','bollywood'],
 ['Dangal','2016','2h 41m','8.3','Drama · Sport','A wrestler trains his daughters to become champions.','https://image.tmdb.org/t/p/w500/lMH5BTVjb6VhQZTBqzQ4RzFLqWQ.jpg','movie','bollywood'],
 ['Bajrangi Bhaijaan','2015','2h 43m','7.6','Drama · Comedy','A devotee of Hanuman helps a mute Pakistani girl.','https://image.tmdb.org/t/p/w500/5mEtdH2Rzn6zOSf7Mq3DaEGGBcO.jpg','movie','bollywood'],
 ['Dilwale Dulhania Le Jayenge','1995','3h 9m','8.1','Romance · Drama','A couple fall in love on a trip to Europe.','https://image.tmdb.org/t/p/w500/2CAL2433ZeIihfX1Hb2139CX0pW.jpg','movie','bollywood'],
 ['Pushpa: The Rise','2021','2h 59m','7.5','Action · Crime','A laborer rises against the smuggling network.','https://image.tmdb.org/t/p/w500/g7I8iKc5fELJSKQKT8WFLW3q1m9.jpg','movie','action'],
 ['Uri: The Surgical Strike','2019','2h 18m','8.2','Action · Drama','India retaliates against Pakistan with a military strike.','https://image.tmdb.org/t/p/w500/3xGVn2T9bTHPRu0OJHH6dXrxEeB.jpg','movie','action'],
 ['Baahubali 2: The Conclusion','2017','2h 47m','8.2','Action · Drama','Shiva learns about his father Baahubali.','https://image.tmdb.org/t/p/w500/t8B2biqkqwsLFnQGQNiKPqUoGnJ.jpg','movie','action'],
 ['Money Heist','2017','5 Parts','8.2','Crime · Thriller','A mysterious professor recruits thieves to carry out heists.','https://image.tmdb.org/t/p/w500/reEMJA1uzscCbkpeRJeTT2bjqUp.jpg','series','trending'],
 ['Breaking Bad','2008','5 Seasons','9.5','Crime · Drama','A chemistry teacher turns to drug manufacturing.','https://image.tmdb.org/t/p/w500/ggFHVNu6YYI5L9pCfOacjizRGt.jpg','series','top10'],
 ['Stranger Things','2016','4 Seasons','8.7','Sci-Fi · Horror','A boy vanishes in a small Indiana town.','https://image.tmdb.org/t/p/w500/49WJfeN0moxb9IPfGn8AIqMGskD.jpg','series','trending'],
 ['Game of Thrones','2011','8 Seasons','9.2','Fantasy · Drama','Noble families fight for control of the Iron Throne.','https://image.tmdb.org/t/p/w500/1XS1oqL89opfnbLl8WnZY1O1uJx.jpg','series','top10'],
 ['The Last of Us','2023','2 Seasons','8.8','Drama · Sci-Fi','A man escorts a girl through a zombie-infested world.','https://image.tmdb.org/t/p/w500/uKvVjHNqB5VmOrdxqAt2F7J78ED.jpg','series','trending'],
 ['Squid Game','2021','2 Seasons','8.0','Thriller · Drama','Contestants play deadly children\'s games for prize money.','https://image.tmdb.org/t/p/w500/dDlEmu3EZ0Pgg93K2SVNLCjCSvE.jpg','series','trending'],
 ['Narcos','2015','3 Seasons','8.8','Crime · Drama','The story of Colombian drug lord Pablo Escobar.','https://image.tmdb.org/t/p/w500/rTmal9fDbwh5F0waol2hq35U4ah.jpg','series','top10'],
 ['Peaky Blinders','2013','6 Seasons','8.8','Crime · Drama','A gang navigates post-WWI Birmingham.','https://image.tmdb.org/t/p/w500/vUUqzWa2LnHIVqkaKVlVGkPmppb.jpg','series','trending'],
 ['The Witcher','2019','3 Seasons','8.2','Fantasy · Action','A mutated monster-hunter navigates a magical world.','https://image.tmdb.org/t/p/w500/cZ0d3rtvXPVvuiX22sP79K3Hmjz.jpg','series','trending'],
 ['Wednesday','2022','1 Season','8.1','Comedy · Fantasy','Wednesday Addams navigates life at Nevermore Academy.','https://image.tmdb.org/t/p/w500/9PFonBhy4cQy7Jz20NpMygczOkv.jpg','series','trending'],
 ['Ozark','2017','4 Seasons','8.4','Crime · Drama','A financial advisor launders money for a drug cartel.','https://image.tmdb.org/t/p/w500/pSgXKPU5h6U89oDcCbBHeERFiZp.jpg','series','top10'],
 ['The Crown','2016','6 Seasons','8.6','Drama · History','The reign of Queen Elizabeth II.','https://image.tmdb.org/t/p/w500/1M876KPjulVwppEpldhdc8V4o68.jpg','series','hollywood'],
 ['Sherlock','2010','4 Seasons','9.1','Crime · Mystery','A modern-day Sherlock Holmes solves crimes in London.','https://image.tmdb.org/t/p/w500/7WTsnHkbA0FaG6R9twfFde0I9hl.jpg','series','top10'],
 ['House of the Dragon','2022','2 Seasons','8.4','Fantasy · Drama','The story of the Targaryen civil war.','https://image.tmdb.org/t/p/w500/z2yahl2uefxDCl0nogcRBstwruJ.jpg','series','trending'],
 ['Attack on Titan','2013','4 Seasons','9.0','Animation · Action','Humanity fights giant humanoid creatures.','https://image.tmdb.org/t/p/w500/hTP1DtLGFamjfu8WqjnuQdP1n4i.jpg','series','top10'],
 ['One Piece','2023','1 Season','8.4','Action · Adventure','Luffy and his crew sail the seas to become Pirate King.','https://image.tmdb.org/t/p/w500/r0Q6eeN9L1BgqTBVDilUW7CEHQZ.jpg','series','trending'],
 ['Jujutsu Kaisen','2020','2 Seasons','8.7','Animation · Action','A boy becomes cursed and joins a school of jujutsu sorcerers.','https://image.tmdb.org/t/p/w500/hTP1DtLGFamjfu8WqjnuQdP1n4i.jpg','series','trending'],
 ['The Boys','2019','4 Seasons','8.7','Action · Comedy','A group fights corrupt superheroes.','https://image.tmdb.org/t/p/w500/mY7SeH4HFFxW1hiI6cWuwCRKptN.jpg','series','trending'],
 ['Black Mirror','2011','6 Seasons','8.8','Sci-Fi · Thriller','Dark tales about technology and society.','https://image.tmdb.org/t/p/w500/7PRddO7z7mcPi21nZTCMGShAyy1.jpg','series','top10'],
 ['Dark','2017','3 Seasons','8.8','Mystery · Sci-Fi','Time travel connects four interconnected families.','https://image.tmdb.org/t/p/w500/apbrbWs5M6lMCNFQEP44GStSPYi.jpg','series','top10'],
];

$stmt = $pdo->prepare("INSERT IGNORE INTO content (title,release_year,duration,rating,genre,description,poster_url,content_type,category) VALUES (?,?,?,?,?,?,?,?,?)");
$count = 0;
foreach ($movies as $m) {
    try {
        // Check if exists
        $chk = $pdo->prepare("SELECT id FROM content WHERE title=?");
        $chk->execute([$m[0]]);
        if ($chk->fetch()) continue;
        $stmt->execute($m);
        $count++;
    } catch(Exception $e) { /* skip */ }
}
echo "Added $count new titles to CineVault!\n";
