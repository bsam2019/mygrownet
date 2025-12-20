<div class="tab-panel">
    <div class="settings-group">
        <div class="settings-group-title">Basic Adjustments</div>
        
        <div class="form-group">
            <label>Brightness: <span id="brightnessValue">0</span></label>
            <input type="range" id="brightness" class="slider" min="-100" max="100" value="0">
        </div>
        
        <div class="form-group">
            <label>Contrast: <span id="contrastValue">0</span></label>
            <input type="range" id="contrast" class="slider" min="-100" max="100" value="0">
        </div>
        
        <div class="form-group">
            <label>Saturation: <span id="saturationValue">0</span></label>
            <input type="range" id="saturation" class="slider" min="-100" max="100" value="0">
        </div>
    </div>
    
    <div class="settings-group">
        <div class="settings-group-title">Effects</div>
        
        <div class="form-group">
            <label>Blur: <span id="blurValue">0</span></label>
            <input type="range" id="blur" class="slider" min="0" max="10" value="0">
        </div>
        
        <div class="form-group">
            <label>Hue Rotate: <span id="hueValue">0</span>°</label>
            <input type="range" id="hueRotate" class="slider" min="0" max="360" value="0">
        </div>
        
        <div class="form-group">
            <label class="checkbox-label">
                <input type="checkbox" id="invertColors">
                <span>Invert Colors</span>
            </label>
        </div>
    </div>
    
    <div class="settings-group">
        <div class="settings-group-title">Quick Filters</div>
        <div class="filter-buttons">
            <button type="button" class="btn btn-filter btn-sm" data-filter="grayscale">Grayscale</button>
            <button type="button" class="btn btn-filter btn-sm" data-filter="sepia">Sepia</button>
            <button type="button" class="btn btn-filter btn-sm" data-filter="vintage">Vintage</button>
            <button type="button" class="btn btn-filter btn-sm" data-filter="none">Reset All</button>
        </div>
    </div>
</div>
