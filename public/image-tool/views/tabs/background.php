<div class="tab-panel">
    <div class="settings-group">
        <div class="settings-group-title">🎭 Background Removal</div>
        <p style="margin: 0 0 15px 0; color: #666; font-size: 14px;">
            Remove background from your images automatically
        </p>
        
        <div class="form-group">
            <label>Removal Method</label>
            <select id="bgRemovalMethod" class="form-control">
                <option value="auto">Auto (Sample Corners)</option>
                <option value="white">Remove White Background</option>
                <option value="custom">Custom Color</option>
            </select>
        </div>
        
        <div class="form-group" id="bgCustomColorGroup" style="display: none;">
            <label>Background Color to Remove</label>
            <div style="display: flex; gap: 10px; align-items: center;">
                <input type="color" id="bgCustomColor" value="#ffffff" style="width: 60px; height: 40px; border: 1px solid #ddd; border-radius: 4px; cursor: pointer;">
                <span id="bgCustomColorHex" style="font-size: 13px; color: #666;">#ffffff</span>
            </div>
        </div>
        
        <div class="form-group">
            <label>Tolerance (0-100)</label>
            <div style="display: flex; gap: 10px; align-items: center;">
                <input type="range" id="bgTolerance" min="0" max="100" value="30" class="form-control" style="flex: 1;">
                <span id="bgToleranceValue" style="min-width: 35px; text-align: right; font-weight: 500;">30</span>
            </div>
            <small style="color: #666; font-size: 12px; display: block; margin-top: 5px;">
                Higher values remove more similar colors
            </small>
        </div>
        
        <div class="alert alert-info" style="background: #e3f2fd; border: 1px solid #90caf9; border-radius: 4px; padding: 12px; margin-top: 15px; font-size: 13px;">
            <strong>💡 Tip:</strong> Works best with solid color backgrounds. For complex backgrounds, consider using professional tools or APIs.
        </div>
    </div>
</div>
