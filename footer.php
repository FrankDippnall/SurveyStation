<?php

// Things to notice:
// This script is called by every other script (via require_once)
// It finishes outputting the HTML for this page:
// don't forget to add your name and student number to the footer

echo <<<_END
    </main>
    <footer class="main_footer"><br>6G5Z2107 &copy; Frank Dippnall, 17003003, 2018/19. Icons by <a href='http://icons8.com'>icons8.com</a></footer>

    <script src="js/main.js"></script>
    </body>
    </html>
_END;
?>