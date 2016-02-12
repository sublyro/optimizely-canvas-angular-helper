<div class="data-sidebar">

<h2 class="canvas-sidebar-title">Information</h2>

<div class=""> 
	<div class=""> 
		<ul class="sections list block-list"> 
			<li class="sections__item"> 
				<div class="label"><p>To use this app just select the experiment that you want to run on Angular pages.</p> </div>
			</li>

		</ul>
	</div>
</div>

<h2 class="canvas-sidebar-title">Settings</h2>

<div class=""> 
	<div class=""> 
		<ul class="sections list block-list"> 
			<form id="disable-app" method="POST" action="index.php">
				<input type="hidden" name="action" id="action"/>
				<input type="hidden" name="signed_request" id="signed_request" value="<?php echo $canvas->get_signed_request(); ?>"/>
				<li class="sections__item" data-test-section="integration-sidebar-on-off-section" id="disable-toggle"> <div class="button-group"> <button class="button enable <?php if ($canvas->is_enabled()) {echo('button--highlight');} ?>">On</button> <button class="button disable <?php if (!$canvas->is_enabled()) {echo('button--highlight');} ?>">Off</button> </div> </li>
			</form>
		</ul>
	</div>
</div>

</div>