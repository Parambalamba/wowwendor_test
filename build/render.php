<?php
/**
 * @see https://github.com/WordPress/gutenberg/blob/trunk/docs/reference-guides/block-api/block-metadata.md#render
 */
?>
<h1 <?php echo get_block_wrapper_attributes(); ?>>Pokemon of type <?= $attributes['type_name'] ?></h1>
<div class="pokes-wrapper">
	<?= $attributes['pokes_html']; ?>
</div>

