<div class="tab-panel">
    <div class="settings-group">
        <div class="settings-group-title">Compression</div>
        <div class="form-group">
            <label>Quality: <span id="qualityValue">90</span>%</label>
            <input type="range" id="quality" class="slider" min="1" max="100" value="90">
        </div>
        
        <div class="form-group">
            <label>Output Format</label>
            <select id="outputFormat" class="form-control">
                <option value="same">Keep original</option>
                <option value="jpg">JPEG</option>
                <option value="png">PNG</option>
                <option value="webp">WebP</option>
            </select>
        </div>
    </div>
    
    <div class="settings-group">
        <div class="settings-group-title">Options</div>
        <label class="checkbox-label">
            <input type="checkbox" id="stripMetadata" checked>
            <span>Strip EXIF metadata</span>
        </label>
        
        <label class="checkbox-label">
            <input type="checkbox" id="progressiveJpeg">
            <span>Progressive JPEG</span>
        </label>
    </div>
</div>
