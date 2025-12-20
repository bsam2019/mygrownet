<div class="tab-panel">
    <div class="settings-group">
        <div class="settings-group-title">Quick Presets</div>
        <div class="form-group">
            <select id="resizePreset" class="form-control">
                <option value="">Custom dimensions</option>
                <optgroup label="Hero & Banners">
                    <option value="1920x1080">Desktop Hero (1920×1080)</option>
                    <option value="1440x600">Wide Banner (1440×600)</option>
                    <option value="1200x400">Page Banner (1200×400)</option>
                </optgroup>
                <optgroup label="Cards">
                    <option value="600x400">Service Card (600×400)</option>
                    <option value="400x300">Product Card (400×300)</option>
                    <option value="300x300">Square (300×300)</option>
                </optgroup>
                <optgroup label="Thumbnails">
                    <option value="150x150">Small (150×150)</option>
                    <option value="200x200">Medium (200×200)</option>
                </optgroup>
                <optgroup label="Social Media">
                    <option value="1200x630">FB/LinkedIn (1200×630)</option>
                    <option value="1080x1080">Instagram (1080×1080)</option>
                </optgroup>
            </select>
        </div>
    </div>
    
    <div class="settings-group">
        <div class="settings-group-title">Dimensions</div>
        <div class="form-row">
            <div class="form-group">
                <label>Width (px)</label>
                <input type="number" id="resizeWidth" class="form-control" value="800" min="1" max="5000">
            </div>
            <div class="form-group">
                <label>Height (px)</label>
                <input type="number" id="resizeHeight" class="form-control" placeholder="Auto" min="1" max="5000">
            </div>
        </div>
        
        <div class="form-group">
            <label>Fit Mode</label>
            <select id="resizeFit" class="form-control">
                <option value="exact">Exact (may stretch)</option>
                <option value="contain">Contain (fit inside)</option>
                <option value="cover">Cover (fill area)</option>
            </select>
        </div>
        
        <label class="checkbox-label">
            <input type="checkbox" id="maintainAspect">
            <span>Maintain aspect ratio</span>
        </label>
    </div>
</div>
