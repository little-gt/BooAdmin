<?php if(!defined('__TYPECHO_ADMIN__')) exit; ?>

<?php
if (isset($post) || isset($page)) {
    $cid = isset($post) ? $post->cid : $page->cid;

    if ($cid) {
        \Widget\Contents\Attachment\Related::alloc(['parentId' => $cid])->to($attachment);
    } else {
        \Widget\Contents\Attachment\Unattached::alloc()->to($attachment);
    }
}
?>

<div id="upload-panel" class="p-4 bg-gray-50 border border-dashed border-gray-300 hover:border-discord-accent transition-colors">
    <div class="upload-area text-center py-8 cursor-pointer text-discord-muted hover:text-discord-text" data-url="<?php echo $security->getTokenUrl($security->getIndex('/action/upload')); ?>">
        <div class="mb-3 text-4xl text-gray-300">
            <i class="fas fa-cloud-upload-alt"></i>
        </div>
        <p class="text-sm"><?php _e('拖放文件到这里<br>或者 %s选择文件上传%s', '<a href="###" class="upload-file text-discord-accent hover:underline font-medium">', '</a>'); ?></p>
    </div>
    <ul id="file-list" class="mt-4 space-y-2">
    <?php while ($attachment->next()): ?>
        <li class="group flex items-center justify-between p-2 bg-white border border-gray-200 hover:border-discord-accent transition-colors" data-cid="<?php $attachment->cid(); ?>" data-url="<?php echo $attachment->attachment->url; ?>" data-image="<?php echo $attachment->attachment->isImage ? 1 : 0; ?>">
            <input type="hidden" name="attachment[]" value="<?php $attachment->cid(); ?>" />
            <a class="insert flex-1 text-sm text-discord-text hover:text-discord-accent truncate mr-2" title="<?php _e('点击插入文件'); ?>" href="###">
                <i class="far fa-file mr-2 text-gray-400"></i>
                <?php $attachment->title(); ?>
            </a>
            <div class="info text-xs text-gray-400 flex items-center space-x-2">
                <span><?php echo number_format(ceil($attachment->attachment->size / 1024)); ?> Kb</span>
                <a class="file text-gray-400 hover:text-discord-accent" target="_blank" href="<?php $options->adminUrl('media.php'); ?>?cid=<?php $attachment->cid(); ?>" title="<?php _e('编辑'); ?>"><i class="fas fa-edit"></i></a>
                <a href="###" class="delete text-gray-400 hover:text-red-500" title="<?php _e('删除'); ?>"><i class="fas fa-trash-alt"></i></a>
            </div>
        </li>
    <?php endwhile; ?>
    </ul>
</div>

