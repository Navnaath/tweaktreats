-- Create database
CREATE DATABASE IF NOT EXISTS tweaktreats;
USE tweaktreats;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    image_url VARCHAR(255),
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL
);

-- Recipe categories table
CREATE TABLE IF NOT EXISTS recipe_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL
);

-- Recipes table
CREATE TABLE IF NOT EXISTS recipes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    ingredients TEXT NOT NULL, -- JSON array
    instructions TEXT NOT NULL, -- JSON array
    prep_time INT NOT NULL,
    cook_time INT NOT NULL,
    servings INT NOT NULL,
    difficulty ENUM('easy', 'medium', 'hard') NOT NULL,
    image_url VARCHAR(255),
    category_id INT,
    created_by INT,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    FOREIGN KEY (category_id) REFERENCES recipe_categories(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- User favorites table
CREATE TABLE IF NOT EXISTS user_favorites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    recipe_id INT NOT NULL,
    created_at DATETIME NOT NULL,
    UNIQUE KEY (user_id, recipe_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (recipe_id) REFERENCES recipes(id) ON DELETE CASCADE
);

-- Ingredient densities table
CREATE TABLE IF NOT EXISTS ingredient_densities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    density_grams_per_cup FLOAT NOT NULL,
    density_grams_per_tablespoon FLOAT NOT NULL,
    density_grams_per_teaspoon FLOAT NOT NULL,
    submitted_by INT,
    verified BOOLEAN NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    FOREIGN KEY (submitted_by) REFERENCES users(id)
);

-- Insert sample data
INSERT INTO recipe_categories (name, created_at, updated_at) VALUES
('Cakes', NOW(), NOW()),
('Cookies', NOW(), NOW()),
('Breads', NOW(), NOW()),
('Pies', NOW(), NOW()),
('Pastries', NOW(), NOW()),
('Desserts', NOW(), NOW());

-- Insert sample ingredient densities
INSERT INTO ingredient_densities (name, density_grams_per_cup, density_grams_per_tablespoon, density_grams_per_teaspoon, verified, created_at, updated_at) VALUES
('flour', 120, 7.5, 2.5, 1, NOW(), NOW()),
('sugar', 200, 12.5, 4.2, 1, NOW(), NOW()),
('brown_sugar', 220, 13.8, 4.6, 1, NOW(), NOW()),
('butter', 227, 14.2, 4.7, 1, NOW(), NOW()),
('milk', 240, 15, 5, 1, NOW(), NOW()),
('cocoa', 85, 5.3, 1.8, 1, NOW(), NOW()),
('honey', 340, 21.3, 7.1, 1, NOW(), NOW()),
('oil', 218, 13.6, 4.5, 1, NOW(), NOW());

-- Insert sample recipes
INSERT INTO recipes (name, description, ingredients, instructions, prep_time, cook_time, servings, difficulty, category_id, created_at, updated_at) VALUES
(
    'Classic Chocolate Chip Cookies',
    'Soft and chewy chocolate chip cookies with a perfect golden edge.',
    '[
        "2 1/4 cups all-purpose flour (270g)",
        "1 teaspoon baking soda",
        "1 teaspoon salt",
        "1 cup unsalted butter, softened (227g)",
        "3/4 cup granulated sugar (150g)",
        "3/4 cup packed brown sugar (165g)",
        "2 large eggs",
        "2 teaspoons vanilla extract",
        "2 cups semi-sweet chocolate chips (340g)"
    ]',
    '[
        "Preheat oven to 375°F (190°C).",
        "In a small bowl, mix flour, baking soda, and salt.",
        "In a large bowl, cream together butter and sugars until light and fluffy.",
        "Beat in eggs one at a time, then stir in vanilla.",
        "Gradually blend in the dry ingredients.",
        "Fold in chocolate chips.",
        "Drop by rounded tablespoons onto ungreased baking sheets.",
        "Bake for 9 to 11 minutes or until golden brown.",
        "Let stand on baking sheet for 2 minutes, then transfer to wire racks to cool completely."
    ]',
    15, 10, 24, 'easy', 2, NOW(), NOW()
),
(
    'Vanilla Sponge Cake',
    'Light and fluffy vanilla sponge cake, perfect for layering.',
    '[
        "2 cups all-purpose flour (240g)",
        "2 teaspoons baking powder",
        "1/2 teaspoon salt",
        "1/2 cup unsalted butter, softened (113g)",
        "1 1/2 cups granulated sugar (300g)",
        "2 large eggs",
        "2 teaspoons vanilla extract",
        "1 cup milk (240ml)"
    ]',
    '[
        "Preheat oven to 350°F (175°C). Grease and flour two 9-inch round cake pans.",
        "In a medium bowl, whisk together flour, baking powder, and salt.",
        "In a large bowl, cream together butter and sugar until light and fluffy.",
        "Beat in eggs one at a time, then stir in vanilla.",
        "Gradually add the dry ingredients to the wet ingredients, alternating with milk.",
        "Pour batter evenly into the prepared pans.",
        "Bake for 30 to 35 minutes or until a toothpick inserted into the center comes out clean.",
        "Allow to cool in pans for 10 minutes, then remove to wire racks to cool completely."
    ]',
    20, 35, 12, 'medium', 1, NOW(), NOW()
),
(
    'Fudgy Brownies',
    'Rich, fudgy chocolate brownies with a crackly top.',
    '[
        "1/2 cup unsalted butter (113g)",
        "1 cup granulated sugar (200g)",
        "2 large eggs",
        "1 teaspoon vanilla extract",
        "1/2 cup all-purpose flour (60g)",
        "1/3 cup unsweetened cocoa powder (40g)",
        "1/4 teaspoon salt",
        "1/4 teaspoon baking powder"
    ]',
    '[
        "Preheat oven to 350°F (175°C). Line an 8-inch square baking pan with parchment paper.",
        "Melt butter in a microwave-safe bowl.",
        "Stir in sugar, eggs, and vanilla extract.",
        "In a separate bowl, whisk together flour, cocoa powder, salt, and baking powder.",
        "Add dry ingredients to wet ingredients and mix until just combined.",
        "Pour batter into the prepared pan and spread evenly.",
        "Bake for 25 to 30 minutes or until a toothpick inserted in the center comes out with a few moist crumbs.",
        "Allow to cool completely before cutting into squares."
    ]',
    15, 30, 9, 'easy', 6, NOW(), NOW()
);

