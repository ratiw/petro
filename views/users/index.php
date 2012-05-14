<?php echo isset($scopes) ? $scopes : ''; ?>

<div class="paginated_collection">
	<?php echo isset($page_info) ? $page_info : ''; ?>
	<div class="paginated_collection_contents">
		<div class="index_content">
			<?php echo isset($grid) ? $grid : ''; ?>
		</div>
	</div>
	<div id="index_footer">
		<nav class="pagination">
			<?php echo $pagination; ?>
		</nav>
	</div>
</div>

