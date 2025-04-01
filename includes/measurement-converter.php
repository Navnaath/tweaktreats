<div class="card-header">
    <h2>Convert Measurements</h2>
</div>
<div class="card-content">
    <form id="measurement-form" class="measurement-form">
        <div class="form-grid">
            <div class="form-group">
                <label for="amount">Amount</label>
                <input type="number" id="amount" name="amount" step="0.01" min="0" placeholder="1.5" required>
            </div>
            <div class="form-group">
                <label for="unit">Unit</label>
                <select id="unit" name="unit" required>
                    <option value="cup">Cup</option>
                    <option value="tablespoon">Tablespoon</option>
                    <option value="teaspoon">Teaspoon</option>
                    <option value="ounce">Ounce</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="ingredient">Ingredient</label>
            <select id="ingredient" name="ingredient" required>
                <option value="flour">All-purpose Flour</option>
                <option value="sugar">Granulated Sugar</option>
                <option value="brown_sugar">Brown Sugar</option>
                <option value="butter">Butter</option>
                <option value="milk">Milk</option>
                <option value="cocoa">Cocoa Powder</option>
                <option value="honey">Honey</option>
                <option value="oil">Vegetable Oil</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary btn-block">
            <i class="fas fa-balance-scale"></i> Convert to Grams
        </button>
    </form>

    <div id="conversion-result" class="conversion-result"></div>
</div>

