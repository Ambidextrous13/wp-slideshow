<?php
/**
 * Manages front-end scripts for the admin-side plugin page.
 *
 * @package wordpress_slideshow
 * @author t0nystark <https://profiles.wordpress.org/t0nystark/>
 */

 ?>
<div id="accordion">
	<h3 class="accordion-heading"> Settings</h3>
	<div id="settings">
		<div class="preview-size">
			<fieldset>
				<legend>Preview Slide Shape: </legend>
				<label title = "Make preview slide square" for="square">Square(Recommended)</label>
				<input type="radio" name="radio-shape" id="square" value="1" checked>
				
				<label for="rectangle">Rectangle</label>
				<input type="radio" name="radio-shape" id="rectangle" value="0">
			</fieldset>
			
			<fieldset>
				<label for="preview_width">Preview Slide Width</label>
				<input type="text" id="preview_width" readonly style="border:0; color:#f6931f; font-weight:bold;">
				<div id="slider_width" class="slider"></div>
			</fieldset>
			<fieldset id="preview_height_enc" class="dp-none">
				<label for="preview_height">Preview Slide Height</label>
				<input type="text" id="preview_height" readonly style="border:0; color:#f6931f; font-weight:bold;">
				<div id="slider_height" class="slider"></div>
			</fieldset>
		</div>
		<div class="webview-size">
			<fieldset>
				<legend>Actual Slide Shape: </legend>
				<label for="square-wv">Square(Recommended)</label>
				<input type="radio" name="radio-shape-wv" id="square-wv" value="1" checked>
				
				<label for="rectangle-wv">Rectangle</label>
				<input type="radio" name="radio-shape-wv" id="rectangle-wv" value="0">
			</fieldset>
			
			<fieldset>
				<label for="webview_width">Actual Slide Width</label>
				<input type="text" id="webview_width" readonly style="border:0; color:#f6931f; font-weight:bold;">
				<div id="slider_width_wv" class="slider"></div>
			</fieldset>
			<fieldset id="webview_height_enc" class="dp-none">
				<label for="webview_height">Actual Slide Height</label>
				<input type="text" id="webview_height" readonly style="border:0; color:#f6931f; font-weight:bold;">
				<div id="slider_height_wv" class="slider"></div>
			</fieldset>
		</div>
		<div class="slide-limit">
			<fieldset>
				<legend>Slide Range Selector: </legend>
				<label for="limit">Enable</label>
				<input type="radio" name="radio-slide-limit" id="limit" value="1" checked>

				<label for="no-limit">Disable</label>
				<input type="radio" name="radio-slide-limit" id="no-limit" value="0">
			</fieldset>
			<fieldset id="slide-range-set">
				<label for="slide-range">Slides Range:</label>
				<input type="text" id="slide-range" readonly style="border:0; color:#f6931f; font-weight:bold;">
				<div id="slider-range" class="slider"></div>
			</fieldset>
		</div>
	</div>
	
	<h3 class="accordion-heading">Preview</h3>
	<div>
		<form id="wpss-form" action="">
			<label for="upload">Add more slides</label>
			<input type="file" name="files" id="files" accept="image/*" multiple>
			<button type="button" name="upload" id="upload" >Upload</button>
			<div id="sortable" class="slides-container">

				<div data="img-1" class="ui-state-default img-holder img-1"><div class="slide-delete"></div>1</div>
				<div data="img-2" class="ui-state-default img-holder img-2"><div class="slide-delete"></div>2</div>
				<div data="img-3" class="ui-state-default img-holder img-3"><div class="slide-delete"></div>3</div>
				<div data="img-4" class="ui-state-default img-holder img-4"><div class="slide-delete"></div>4</div>
				<div data="img-5" class="ui-state-default img-holder img-5"><div class="slide-delete"></div>5</div>
				<div data="img-6" class="ui-state-default img-holder img-6"><div class="slide-delete"></div>6</div>
				<div data="img-7" class="ui-state-default img-holder img-7"><div class="slide-delete"></div>7</div>
				<div data="img-8" class="ui-state-default img-holder img-8"><div class="slide-delete"></div>8</div>
				<div data="img-9" class="ui-state-default img-holder img-9"><div class="slide-delete"></div>9</div>
				<div data="img-10" class="ui-state-default img-holder img-10"><div class="slide-delete"></div>10</div>
				<div data="img-11" class="ui-state-default img-holder img-11"><div class="slide-delete"></div>11</div>
				<div data="img-12" class="ui-state-default img-holder img-12"><div class="slide-delete"></div>12</div>
				<div data="img-13" class="ui-state-default img-holder img-13"><div class="slide-delete"></div>13</div>
				<div data="img-14" class="ui-state-default img-holder img-14"><div class="slide-delete"></div>14</div>
				<div data="img-15" class="ui-state-default img-holder img-15"><div class="slide-delete"></div>15</div>
			</div>
		</form>
		<div id="delete-dialogue">
			<div class="delete-caution">
				<div class="caution-logo"></div>
				<p class="caution-text">Caution</p>
			</div>
			<p class="delete-maintext">Confirm Delete</p>
			<div id="slide-delete-buttons">
				<button class="slide-delete-button">Cancel</button>
				<button class="slide-delete-button">Confirm</button>
			</div>
		</div>
	</div>
</div>