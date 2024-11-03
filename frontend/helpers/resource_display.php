<?php
function getResourceIcon($resource_key, $display_name = null) {
    if ($display_name === null) {
        $display_name = ucwords(str_replace('_', ' ', $resource_key));
    }
    return "<img src='resources/{$resource_key}_icon.png' alt='{$resource_key}' title='{$display_name}' class='resource-icon'>";
}

function formatResourceWithIcon($resource_key, $amount, $display_name = null) {
    return getResourceIcon($resource_key, $display_name) . "" . number_format($amount);
}
?>

<style>
.resource-icon {
    width: 16px;
    height: 16px;
    vertical-align: middle;
    margin-right: 4px;
}

.resource-icon:hover::after {
    content: attr(title);
    position: absolute;
    background: #333;
    color: white;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    white-space: nowrap;
    z-index: 1000;
    margin-left: 8px;
}
</style> 