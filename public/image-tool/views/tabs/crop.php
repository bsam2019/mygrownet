<div class="tab-panel">
    <div class="settings-group">
        <div class="settings-group-title">✂️ Crop Settings</div>
        <p style="margin: 0 0 15px 0; color: #666; font-size: 14px;">
            Drag the corners to resize, drag inside to move the crop area
        </p>
        
        <div class="form-group">
            <label>Aspect Ratio</label>
            <select id="cropAspect" class="form-control">
                <option value="free">Free (Any Size)</option>
                <option value="1:1">Square (1:1)</option>
                <option value="16:9">Widescreen (16:9)</option>
                <option value="4:3">Standard (4:3)</option>
                <option value="3:2">Photo (3:2)</option>
                <option value="9:16">Portrait (9:16)</option>
            </select>
        </div>
        
        <div class="form-group">
            <label>Shape</label>
            <select id="cropShape" class="form-control">
                <option value="rectangle">Rectangle</option>
                <option value="circle">Circle</option>
            </select>
        </div>
        
        <div class="crop-controls" id="cropControls" style="display: none; gap: 8px; flex-wrap: wrap; margin-top: 15px;">
            <button type="button" class="btn btn-sm" onclick="cropRotate(-90)" title="Rotate Left">↶ Rotate Left</button>
            <button type="button" class="btn btn-sm" onclick="cropRotate(90)" title="Rotate Right">↷ Rotate Right</button>
            <button type="button" class="btn btn-sm" onclick="cropFlip('h')" title="Flip Horizontal">⇄ Flip H</button>
            <button type="button" class="btn btn-sm" onclick="cropFlip('v')" title="Flip Vertical">⇅ Flip V</button>
            <button type="button" class="btn btn-sm" onclick="cropReset()" title="Reset Crop">↺ Reset</button>
        </div>
    </div>
    
    <div class="settings-group">
        <div class="settings-group-title">📏 Resize After Crop (Optional)</div>
        <div class="form-group">
            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                <input type="checkbox" id="cropThenResize" style="width: auto;">
                <span>Resize cropped image</span>
            </label>
        </div>
        
        <div id="cropResizeOptions" style="display: none;">
            <div class="form-group">
                <label>Width (px)</label>
                <input type="number" id="cropResizeWidth" class="form-control" placeholder="e.g., 800">
            </div>
            
            <div class="form-group">
                <label>Height (px)</label>
                <input type="number" id="cropResizeHeight" class="form-control" placeholder="Leave empty for auto">
            </div>
            
            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                    <input type="checkbox" id="cropResizeMaintainAspect" checked style="width: auto;">
                    <span>Maintain aspect ratio</span>
                </label>
            </div>
        </div>
    </div>
</div>

<style>
.crop-preview-container {
    max-width: 100%;
    max-height: 600px;
    overflow: hidden;
}

.crop-preview-container img {
    display: block;
    max-width: 100%;
}

.crop-controls {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.crop-controls .btn-sm {
    padding: 6px 12px;
    font-size: 13px;
    flex: 1;
    min-width: 100px;
}
</style>
