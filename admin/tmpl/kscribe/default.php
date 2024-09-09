<?php
defined('_JEXEC') or die;

use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;

?>
<form action="<?php echo Route::_('index.php?option=com_kscribe&view=kscribe'); ?>" method="post" name="adminForm" id="adminForm">
    <div id="j-main-container" class="j-main-container">
        <?php
        // Search tools bar
        echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this]);
        ?>
       <?php if (empty($this->items)) : ?>
            <div class="alert alert-info">
                <span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
                <?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
            </div>
        <?php else : ?>
            <table class="table" id="tagList">
                <caption class="visually-hidden">
                    <?php echo Text::_('COM_KSCRIBE_TABLE_CAPTION'); ?>,
                            <span id="orderedBy"><?php echo Text::_('JGLOBAL_SORTED_BY'); ?> </span>,
                            <span id="filteredBy"><?php echo Text::_('JGLOBAL_FILTERED_BY'); ?></span>
                </caption>
                <thead>
                    <tr>
                        <td class="w-1 text-center">
                            <?php echo HTMLHelper::_('grid.checkall'); ?>
                        </td>
                        <th scope="col" class="w-1 text-center">
                            <?php echo Text::_('JSTATUS'); ?>
                        </th>
                        <th scope="col">
                            <?php echo Text::_('JGLOBAL_TITLE'); ?>
                        </th>
                        <th scope="col" class="text-center">
                            <?php echo Text::_('COM_KSCRIBE_SUBSCRIBERS'); ?>
                        </th>
                        <th scope="col" class="text-center">
                            <?php echo Text::_('COM_KSCRIBE_AUTOSUB'); ?>
                        </th>
                        <th scope="col" class="w-5 d-none d-md-table-cell">
                            <?php echo Text::_('JGRID_HEADING_ID'); ?>
                        </th>
                    </tr>
                </thead>
                <tbody>
                <?php
                foreach ($this->items as $i => $item) :
                    $orderkey   = array_search($item->id, $this->ordering[$item->parentid]);
                    $canCreate  = true;	//$user->authorise('core.create', 'com_tags');
                    $canEdit    = true;	//$user->authorise('core.edit', 'com_tags');
                    $canCheckin = true;	//$user->authorise('core.manage', 'com_checkin') || $item->checked_out == $user->get('id') || is_null($item->checked_out);
                    $canChange  = true;	//$user->authorise('core.edit.state', 'com_tags') && $canCheckin;

                    // Get the parents of item for sorting
                    if ($item->level > 1) {
                        $parentsStr = '';
                        $_currentParentId = $item->parentid;
                        $parentsStr = ' ' . $_currentParentId;
                        for ($j = 0; $j < $item->level; $j++) {
                            foreach ($this->ordering as $k => $v) {
                                $v = implode('-', $v);
                                $v = '-' . $v . '-';
                                if (strpos($v, '-' . $_currentParentId . '-') !== false) {
                                    $parentsStr .= ' ' . $k;
                                    $_currentParentId = $k;
                                    break;
                                }
                            }
                        }
                    } else {
                        $parentsStr = '';
                    }

                    if (empty($this->autoCats)) {
                    	$autop1 = 'times notenabled';
                    	$autop2 = $item->id . ', 1';
                    } else {
                    	if (isset($this->autoCats[$item->id]) && $this->autoCats[$item->id]['subscribe']) {
	                    	$autop1 = 'check auto-subbed';
	                    	$autop2 = $item->id . ', 0';
                    	} else {
	                    	$autop1 = 'times notenabled';
	                    	$autop2 = $item->id . ', 1';
                    	}
                    }
                    ?>
                    <tr class="row<?php echo $i % 2; ?>" data-draggable-group="<?php echo $item->parentid; ?>"
                        data-item-id="<?php echo $item->id; ?>" data-parents="<?php echo $parentsStr; ?>"
                        data-level="<?php echo $item->level; ?>">
                        <td class="text-center">
                            <?php echo HTMLHelper::_('grid.id', $i, $item->id, false, 'cid', 'cb', $item->name); ?>
                        </td>
                        <td class="text-center">
                            <?php echo HTMLHelper::_('jgrid.published', $item->published, $i, 'kscribe.',false/*$canChange*/); ?>
                        </td>
                        <th scope="row">
                            <?php echo LayoutHelper::render('joomla.html.treeprefix', ['level' => $item->level]); ?>
                            <?php if ($item->checked_out) : ?>
                                <?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'kscribe.', $canCheckin); ?>
                            <?php endif; ?>
                            <?php if ($canEdit) : ?>
                                <a href="<?php echo Route::_('index.php?option=com_kscribe&task=category.edit&id=' . $item->id); ?>" title="<?php echo Text::_('JACTION_EDIT'); ?> <?php echo $this->escape($item->name); ?>">
                                    <?php echo $this->escape($item->name); ?></a>
                            <?php else : ?>
                                <?php echo $this->escape($item->name); ?>
                            <?php endif; ?>
                            <div class="small">
                                <?php echo Text::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)); ?>
                           </div>
                        </th>
                        <td class="text-center">
                        	<?php echo (int) $item->subscribers; ?>
                        </td>
                        <td class="text-center">
                        	<span class="icon-<?php echo $autop1; ?> sel-auto" onclick="Kscribe.setAuto(<?php echo $autop2; ?>, this)"> </span>
                        </td>
                        <td class="d-none d-md-table-cell">
                            <?php echo (int) $item->id; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

            <?php // load the pagination. ?>
            <?php // echo $this->pagination->getListFooter(); ?>

        <?php endif; ?>

        <input type="hidden" name="task" value="">
        <input type="hidden" name="boxchecked" value="0">
        <?php echo HTMLHelper::_('form.token'); ?>
	</div>
</form>
<!--
<p>HOWDY DOODY</p>
<pre id="KFusers"></pre>
<pre id="KFcats"></pre>
<script>
	const formData = new FormData();
	let json = true;
	formData.set('task', 'Json.doAjax');
	formData.set('variable', 'somedata');
	fetch('index.php?option=com_kscribe&format=json', {method:'POST',body:formData})
	.then(resp => { if (!resp.ok) throw new Error(`HTTP ${resp.status}`); if (json) return resp.json(); else return resp.text() })
	.then(data => {
		if (data.error) {
			alert(data.message);
		} else {
			document.getElementById('KFusers').innerHTML = data.users;
			document.getElementById('KFcats').innerHTML = JSON.stringify(data.cats, null, "  ");
		}
	})
	.catch(err => alert('Failure: '+err));
</script>
-->
