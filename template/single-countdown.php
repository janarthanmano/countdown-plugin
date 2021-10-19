<?php
/**
 * The template for displaying all single countdown post
 *
 */

get_header();

// Start the Loop.
while ( have_posts() ) :
    the_post();

    ?>
    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
        <?php
            $expiry_date = get_post_meta( get_the_ID(), '_sbr_expiry-date')[0];
            $datetime1 = new DateTime();
            $datetime2 = new DateTime($expiry_date);
            if ($datetime1 > $datetime2) {
                ?>
                <header class="alignwide">
                    <h2>Page expired</h2>
                </header><!-- .entry-header -->
                <?php
            }else {
                ?>

                <script type="application/javascript">
                    function makeTimer(expiry_date) {

                        var endTime = new Date(expiry_date);
                        endTime = (Date.parse(endTime) / 1000);

                        var now = new Date();
                        now = (Date.parse(now) / 1000);

                        var timeLeft = endTime - now;

                        var days = Math.floor(timeLeft / 86400);
                        var hours = Math.floor((timeLeft - (days * 86400)) / 3600);
                        var minutes = Math.floor((timeLeft - (days * 86400) - (hours * 3600 )) / 60);
                        var seconds = Math.floor((timeLeft - (days * 86400) - (hours * 3600) - (minutes * 60)));

                        if (hours < "10") { hours = "0" + hours; }
                        if (minutes < "10") { minutes = "0" + minutes; }
                        if (seconds < "10") { seconds = "0" + seconds; }

                        $("#days").html(days);
                        $("#hours").html(hours);
                        $("#minutes").html(minutes);
                        $("#seconds").html(seconds);

                    }
                    expiry_date = '<?= $expiry_date ?>';
                    if((typeof expiry_date !== 'undefined')){
                        setInterval(function () {
                            makeTimer(expiry_date);
                        }, 1000);
                    }
                </script>

                <div class="countdown-container countdown container">
                    <p class="text-center pt-5 mt-5 text2">Expires on: </p>
                    <div class="clock row">

                        <!-- days -->
                        <div class="clock-item clock-days countdown-time-value col-sm-6 col-md-3">
                            <div class="wrap">
                                <div class="inner">
                                    <div id="canvas_days" class="clock-canvas"></div>
                                    <div class="text">
                                        <p id="days" class="val"></p>
                                        <p class="type-days type-time">DAYS</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- hours -->

                        <div class="clock-item clock-hours countdown-time-value col-sm-6 col-md-3">
                            <div class="wrap">
                                <div class="inner">
                                    <div id="canvas_hours" class="clock-canvas"></div>
                                    <div class="text">
                                        <p id="hours" class="val"></p>
                                        <p class="type-hours type-time">HOURS</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- minutes -->
                        <div class="clock-item clock-minutes countdown-time-value col-sm-6 col-md-3">
                            <div class="wrap">
                                <div class="inner">
                                    <div id="canvas_minutes" class="clock-canvas"></div>
                                    <div class="text">
                                        <p id="minutes" class="val"></p>
                                        <p class="type-minutes type-time">MINUTES</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- seconds -->
                        <div class="clock-item clock-seconds countdown-time-value col-sm-6 col-md-3">
                            <div class="wrap">
                                <div class="inner">
                                    <div id="canvas_seconds" class="clock-canvas"></div>
                                    <div class="text">
                                        <p id="seconds" class="val"></p>
                                        <p class="type-seconds type-time">SECONDS</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <header class="entry-header alignwide">
                    <?php the_title('<h1 class="entry-title">', '</h1>'); ?>
                </header><!-- .entry-header -->

                <div class="entry-content">
                    <?php
                    the_content();
                    ?>
                </div><!-- .entry-content -->
        <?php
            }
        ?>

    </article><!-- #post-<?php the_ID(); ?> -->
    <?php


endwhile; // End the loop.
get_footer();
