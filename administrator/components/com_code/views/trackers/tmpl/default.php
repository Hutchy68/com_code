<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_code
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));

// For rendering success and failure messages on ajax submmission
JHtml::_('behavior.core');
?>

<script type="text/javascript" src="<?php echo JUri::root() . '/media/editors/tinymce/'; ?>tinymce.min.js"></script>
<script type="text/javascript">
tinymce.init({
    selector: "h3.editable",
    inline: true,
    toolbar: "undo redo",
    menubar: false
});

tinymce.init({
    selector: "div.editable",
    inline: true,
    plugins: [
        "advlist autolink lists link image charmap print preview anchor",
        "searchreplace visualblocks code fullscreen",
        "insertdatetime media table contextmenu paste"
    ],
    toolbar: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image"
});

function saveData()
{
	jQuery(".tracker").each(function() {
		jQuery.ajax({ 
			type:"POST",
			url:'index.php?option=com_code&task=trackers.save&format=json',
			data: {
				"tracker[id]": jQuery(this).data("tracker-id"),
				"tracker[jc_tracker_id]": jQuery(this).data("tracker-jc-id"),
				"tracker[title]": jQuery(this).find("h3").eq(0).text(),
				"tracker[description]": jQuery(this).find(".tracker-description").eq(0).text()
			},
			success:function(response){
				if (response.success && response.data.result)
				{
					Joomla.renderMessages({
						"success": ["Tracker details saved successfully"]
					});

					return false;
				}

				// @todo We should return some more information from the controller about what went wrong 
				Joomla.renderMessages({
					"danger": ["There was an error saving the tracker details"]
				});
			},
			error:function(error){
				// @todo find a more informative way of display such an AJAX error
				Joomla.renderMessages({
					"danger": ["There was an error saving the tracker details"]
				});
			}
		});
	});

	return false;
}
</script>

<div id="j-sidebar-container" class="span2">
	<?php echo $this->sidebar; ?>
</div>
<div id="j-main-container" class="span10">
	<?php if (empty($this->trackers)) : ?>
		<div class="alert alert-no-items">
			<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
		</div>
	<?php else : ?>
		<form class="adminForm">
			<div class="trackers">
				<?php foreach ($this->trackers as $tracker) : ?>
					<div class="tracker" data-tracker-id="<?php echo $tracker->tracker_id; ?>" data-tracker-jc-id="<?php echo $tracker->jc_tracker_id; ?>">
						<h3 class="editable"><?php echo $tracker->title; ?></h3>
						<div class="tracker-description editable">
							<?php echo $tracker->description; ?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
			<button type="submit" class="btn btn-danger" onClick="return saveData();">Save tracker information</button>
		</form>
	<?php endif;?>
</div>
