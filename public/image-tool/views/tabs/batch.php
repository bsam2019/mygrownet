<div class="tab-panel">
    <div class="settings-group">
        <div class="settings-group-title">Batch Mode</div>
        <p style="font-size: 11px; color: #666; margin-bottom: 10px;">Apply operations to all images at once</p>
        
        <div class="form-group">
            <label>Output Naming</label>
            <select id="batchNaming" class="form-control">
                <option value="original">Keep original</option>
                <option value="prefix">Add prefix</option>
                <option value="suffix">Add suffix</option>
                <option value="sequential">Sequential</option>
            </select>
        </div>
        
        <div id="batchPrefixGroup" class="form-group" style="display: none;">
            <label>Prefix</label>
            <input type="text" id="batchPrefix" class="form-control" placeholder="optimized_">
        </div>
        
        <div id="batchSuffixGroup" class="form-group" style="display: none;">
            <label>Suffix</label>
            <input type="text" id="batchSuffix" class="form-control" placeholder="_resized">
        </div>
        
        <div class="form-group">
            <label>Download As</label>
            <select id="batchDownload" class="form-control">
                <option value="zip">ZIP Archive</option>
                <option value="individual">Individual files</option>
            </select>
        </div>
    </div>
</div>
