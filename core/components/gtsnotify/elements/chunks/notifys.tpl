<div class="form-group gtsnotify-channel" data-name="{$name}">
    <div class="input-group">
        <div class="input-group-btn">
            <button class="gtsnotify-channel-btn btn" >
                <span class="{if $count}{$icon}{else}{$icon_empty}{/if}" data-icon="{$icon}" data-icon_empty="{$icon_empty}" title="{$description}"></span>
            </button>
            <span class="badge gtsnotify-badge-notify" {if $count == 0}style="display:none;"{/if}>{$count}</span>
        </div>
    </div>
        <ul class="dropdown-menu gtsnotify-channel-menu  dropdown-menu-right" role="menu">
            
        </ul>
</div>