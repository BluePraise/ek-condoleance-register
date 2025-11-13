<div class="candles-box">
    <?php

    // Get the candles meta value and ensure it's an array
     $candles_meta = get_post_meta(get_the_ID(), 'cmb_condalances_candles', 1);
    if(!is_array($candles_meta)){
        $candles = unserialize($candles_meta);
    }else{
        $candles = get_post_meta(get_the_ID(), 'cmb_condalances_candles', 1);
    }
    $candles = is_array($candles) ? $candles : ['count' => 0, 'authors' => []];
    $string = 'Kaarsen zijn bliksem';

    // Check the count and set the appropriate message
    if (isset($candles['count']) && $candles['count'] == 1) {
        $string = 'Er is 1 kaars aangestoken.';
    } elseif (isset($candles['count']) && $candles['count'] > 1) {
        $string = 'Er zijn ' . intval($candles['count']) . ' kaarsjes aangestoken.';
    }
    ?>

    <form class="candle-form">
        <button type="button" id="light_a_candle" data-id="<?php echo get_the_ID(); ?>" style="margin: 0;" class="gem-button">
            Kaarsje Aansteken
        </button>

        <div class="candle-form__in" style="display:none;">
            <p>Laat je naam achter</p>
            <input type="text" id="candle_name" placeholder="Jouw naam">
            <button type="submit" class="gem-button gem-button-size-tiny">Bevestigen</button>
            <hr>
            <p>
                <label><input type="checkbox" id="candle_anonym" checked>&nbsp;&nbsp; Ik wil anoniem blijven</label>
            </p>
        </div>
    </form>

    <p id="light_a_candle_response_count" class="reverse" style="color: #000; margin-bottom: .5em">
        <?php if (empty($candles['count'])) : ?>
            <!-- Optional: Placeholder if no candles are lit -->
        <?php else: ?>
            <?php echo $string; ?>
        <?php endif; ?>
    </p>

    <p id="light_a_candle_response" style="color: #000; margin-bottom: .5em"></p>

    <?php if (!empty($candles['count'])): ?>
    <p><a id="candle-show-modal">Deze mensen hebben een kaarsje aangestoken</a></p>
    <?php endif; ?>

    <div class="candle-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
        <div class="candle-modal-dialog" style="background: #fff; padding: 20px; border-radius: 5px; position: relative; max-width: 600px; margin: auto;">
            <button type="button" class="candle-modal-close close" style="position: absolute; top: 10px; right: 10px; background: none; border: none; font-size: 20px; cursor: pointer;">×</button>
            <h2><?php echo intval($candles['count']); ?> KAARSJES</h2>
            <div class="candle-authors">
            <?php
            if (!empty($candles['authors']) && is_array($candles['authors']) ) {
                foreach (array_reverse($candles['authors']) as $candleName) {
                    if(isset($candleName[0]) && is_array($candleName[0])){
                        echo "<div><time>{$candleName[0]['candle_date']}</time><p class='candle-author-name'>{$candleName[0]['candle_name']}</p>	</div>";
                    }else{
                        $candleName['candle_name'] = $candleName['candle_name'];
                        echo "<div><time>{$candleName['candle_date']}</time><p class='candle-author-name'>{$candleName['candle_name']}</p>	</div>";
                    }
                }
            }
            ?>
            </div>
        </div>
    </div>

</div>

	<script>
		jQuery('.candle-author-name:contains("Je neukertje ")').parent().hide();
	</script>