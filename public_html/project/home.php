<?php
require(__DIR__ . "/../../partials/nav.php");
?>
<div class="header-title">CINEPEAK MOVIES</div>

<div class="clock" id="clock">Loading time...</div>

<div class="quote" id="quote"></div>

<?php if (is_logged_in(true)) : ?>
    <div class="welcome-msg">Welcome home, <?= get_username(); ?>!</div>
<?php endif; ?>

<script>
    // Update the clock every second
    function updateClock() {
        const clock = document.getElementById("clock");
        const now = new Date();
        clock.innerText = now.toLocaleTimeString();
    }
    setInterval(updateClock, 1000);
    updateClock();

    // Fun static movie quotes
    const quotes = [
        "“Why so serious?” – The Dark Knight",
        "“Here's looking at you, kid.” – Casablanca",
        "“To infinity... and beyond!” – Toy Story",
        "“May the Force be with you.” – Star Wars",
        "“Life finds a way.” – Jurassic Park",
        "“I'm the king of the world!” – Titanic",
        "“Just keep swimming.” – Finding Nemo",
        "“I see dead people.” – The Sixth Sense",
        "“They may take our lives, but they’ll never take our freedom!” – Braveheart",
        "“I feel the need... the need for speed.” – Top Gun"
    ];

    const quoteBox = document.getElementById("quote");
    quoteBox.innerText = quotes[Math.floor(Math.random() * quotes.length)];
</script>

<?php require(__DIR__ . "/../../partials/flash.php"); ?>