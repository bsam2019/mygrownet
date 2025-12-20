<div class="tab-panel">
    <div class="form-group">
        <label>Type</label>
        <select id="watermarkType" class="form-control">
            <option value="text">Text Watermark</option>
            <option value="image">Image/Logo</option>
        </select>
    </div>
    
    <!-- Text Watermark Options -->
    <div id="textWatermarkOptions">
        <!-- Text Content -->
        <div class="settings-group">
            <div class="settings-group-title">Text</div>
            <div class="form-group">
                <input type="text" id="watermarkText" class="form-control" placeholder="© Your Company 2024">
            </div>
        </div>
        
        <!-- Appearance -->
        <div class="settings-group">
            <div class="settings-group-title">Appearance</div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Size</label>
                    <input type="number" id="watermarkFontSize" class="form-control" value="20" min="10" max="100">
                </div>
                <div class="form-group">
                    <label>Opacity</label>
                    <input type="number" id="watermarkOpacity" class="form-control" value="50" min="0" max="100">
                </div>
            </div>
            
            <div class="form-group">
                <label>Color</label>
                <div class="color-swatches-compact">
                    <div class="color-swatch-small active" data-color="255,255,255" style="background: #ffffff; border: 1px solid #ddd;" title="White"></div>
                    <div class="color-swatch-small" data-color="0,0,0" style="background: #000000;" title="Black"></div>
                    <div class="color-swatch-small" data-color="128,128,128" style="background: #808080;" title="Gray"></div>
                    <div class="color-swatch-small" data-color="31,58,138" style="background: #1F3A8A;" title="Blue"></div>
                    <div class="color-swatch-small" data-color="211,47,47" style="background: #D32F2F;" title="Red"></div>
                    <div class="color-swatch-small" data-color="255,215,0" style="background: #FFD700;" title="Gold"></div>
                    <input type="color" id="watermarkCustomColor" class="color-input-inline" value="#ffffff" title="Custom">
                </div>
                <input type="hidden" id="watermarkColor" value="255,255,255">
            </div>
            
            <div class="form-group">
                <label>Font</label>
                <select id="watermarkFont" class="form-control">
                    <option value="1">Small</option>
                    <option value="2">Normal</option>
                    <option value="3" selected>Medium</option>
                    <option value="4">Large</option>
                    <option value="5">Extra Large</option>
                </select>
            </div>
        </div>
        
        <!-- Position -->
        <div class="settings-group">
            <div class="settings-group-title">Position</div>
            
            <div class="form-group">
                <select id="watermarkPosition" class="form-control">
                    <option value="bottom-right">Bottom Right</option>
                    <option value="bottom-left">Bottom Left</option>
                    <option value="top-right">Top Right</option>
                    <option value="top-left">Top Left</option>
                    <option value="center">Center</option>
                </select>
            </div>
        </div>
    </div>
    
    <!-- Image Watermark Options -->
    <div id="imageWatermarkOptions" style="display: none;">
        <div class="settings-group">
            <div class="settings-group-title">Logo Image</div>
            <div class="form-group">
                <input type="file" id="watermarkImage" class="form-control" accept="image/*">
            </div>
        </div>
        
        <div class="settings-group">
            <div class="settings-group-title">Settings</div>
            <div class="form-row">
                <div class="form-group">
                    <label>Scale (%)</label>
                    <input type="number" id="watermarkScale" class="form-control" value="20" min="5" max="50">
                </div>
                <div class="form-group">
                    <label>Opacity (%)</label>
                    <input type="number" id="watermarkImageOpacity" class="form-control" value="50" min="0" max="100">
                </div>
            </div>
            
            <div class="form-group">
                <select id="watermarkPosition" class="form-control">
                    <option value="bottom-right">Bottom Right</option>
                    <option value="bottom-left">Bottom Left</option>
                    <option value="top-right">Top Right</option>
                    <option value="top-left">Top Left</option>
                    <option value="center">Center</option>
                </select>
            </div>
        </div>
    </div>
</div>
