<?php if (!defined('__TYPECHO_ADMIN__')) exit; ?>
<?php
$fields = isset($post) ? $post->getFieldItems() : $page->getFieldItems();
$defaultFields = isset($post) ? $post->getDefaultFieldItems() : $page->getDefaultFieldItems();
?>
<div class="border-t border-gray-100 mt-6 pt-6" id="custom-field">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-bold text-gray-700"><?php _e('自定义字段'); ?></h3>
        <button type="button" class="operate-add text-xs text-discord-accent hover:underline flex items-center">
            <i class="fas fa-plus mr-1"></i> <?php _e('添加字段'); ?>
        </button>
    </div>
    
    <div class="space-y-4 fields">
        <?php foreach ($defaultFields as $field): ?>
            <?php [$label, $input] = $field; ?>
            <div class="field group bg-gray-50 p-3 border border-gray-200">
                <div class="mb-2 text-sm font-medium text-gray-700"><?php $label->render(); ?></div>
                <div class="field-value typecho-reform-style"><?php $input->render(); ?></div>
            </div>
        <?php endforeach; ?>

        <?php foreach ($fields as $field): ?>
            <div class="field group bg-gray-50 p-3 border border-gray-200 relative hover:border-discord-accent transition-colors">
                 <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-2">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium text-gray-500 mb-1"><?php _e('字段名称'); ?></label>
                        <input type="text" name="fieldNames[]" value="<?php echo htmlspecialchars($field['name']); ?>" 
                               pattern="^[_a-zA-Z][_a-zA-Z0-9]*$" oninput="this.reportValidity()" 
                               class="w-full px-2 py-1.5 bg-white border border-gray-300 text-sm focus:outline-none focus:border-discord-accent transition-colors" placeholder="<?php _e('字段名称'); ?>">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1"><?php _e('类型'); ?></label>
                        <select name="fieldTypes[]" class="w-full px-2 py-1.5 bg-white border border-gray-300 text-sm focus:outline-none focus:border-discord-accent transition-colors">
                            <option value="str"<?php if ('str' == $field['type']): ?> selected<?php endif; ?>><?php _e('字符'); ?></option>
                            <option value="int"<?php if ('int' == $field['type']): ?> selected<?php endif; ?>><?php _e('整数'); ?></option>
                            <option value="float"<?php if ('float' == $field['type']): ?> selected<?php endif; ?>><?php _e('小数'); ?></option>
                            <option value="json"<?php if ('json' == $field['type']): ?> selected<?php endif; ?>><?php _e('JSON 结构'); ?></option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1"><?php _e('字段值'); ?></label>
                    <textarea name="fieldValues[]" class="w-full px-2 py-1.5 bg-white border border-gray-300 text-sm focus:outline-none focus:border-discord-accent transition-colors font-mono" rows="2" placeholder="<?php _e('字段值'); ?>"><?php echo htmlspecialchars($field[($field['type'] == 'json' ? 'str' : $field['type']) . '_value']); ?></textarea>
                </div>
                <button type="button" class="btn-delete absolute top-2 right-2 text-gray-400 hover:text-red-500 transition-colors" title="<?php _e('删除'); ?>">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>
        <?php endforeach; ?>
        
        <?php if (empty($defaultFields) && empty($fields)): ?>
            <!-- Initial Empty Field Template for JS to clone logic or just basic one -->
             <div class="field group bg-gray-50 p-3 border border-gray-200 relative hover:border-discord-accent transition-colors">
                 <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-2">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium text-gray-500 mb-1"><?php _e('字段名称'); ?></label>
                        <input type="text" name="fieldNames[]" pattern="^[_a-zA-Z][_a-zA-Z0-9]*$" oninput="this.reportValidity()" 
                               class="w-full px-2 py-1.5 bg-white border border-gray-300 text-sm focus:outline-none focus:border-discord-accent transition-colors" placeholder="<?php _e('字段名称'); ?>">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1"><?php _e('类型'); ?></label>
                        <select name="fieldTypes[]" class="w-full px-2 py-1.5 bg-white border border-gray-300 text-sm focus:outline-none focus:border-discord-accent transition-colors">
                            <option value="str"><?php _e('字符'); ?></option>
                            <option value="int"><?php _e('整数'); ?></option>
                            <option value="float"><?php _e('小数'); ?></option>
                            <option value="json"><?php _e('JSON 结构'); ?></option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1"><?php _e('字段值'); ?></label>
                    <textarea name="fieldValues[]" class="w-full px-2 py-1.5 bg-white border border-gray-300 text-sm focus:outline-none focus:border-discord-accent transition-colors font-mono" rows="2" placeholder="<?php _e('字段值'); ?>"></textarea>
                </div>
                <button type="button" class="btn-delete absolute top-2 right-2 text-gray-400 hover:text-red-500 transition-colors" title="<?php _e('删除'); ?>">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>
        <?php endif; ?>
    </div>
    
     <div class="mt-4 text-xs text-gray-400">
        <i class="fas fa-info-circle mr-1"></i>
        <?php _e('自定义字段可以扩展你的模板功能, 使用方法参见 <a href="https://docs.typecho.org/guide/content/custom-fields" target="_blank" class="text-discord-accent hover:underline">帮助文档</a>'); ?>
    </div>
</div>
