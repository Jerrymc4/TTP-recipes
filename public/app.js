async function apiRequest(path) {
    try {
        const response = await fetch(path, {
            headers: {
                'Accept': 'application/json',
            },
        });

        if (!response.ok) {
            throw new Error(`HTTP error, status ${response.status}`);
        }

        return await response.json();
    } catch (error) {
        console.error(`apiRequest(): ${error}`);
        throw error;
    }
}

function recipesApp() {
    return {
        recipes: [],
        loading: true,
        error: null,
        init() {
            this.fetchRecipes();
        },
        createRecipeComponentFromData (recipeData) {
            return {
                data: recipeData,
                editingData: {},
                editing: false,
                error: null,
            };
        },
        async fetchRecipes() {
            try {
                this.recipes = (await apiRequest('/api/recipes')).map(this.createRecipeComponentFromData);
                this.error = null;
            } catch (error) {
                this.error = 'Error fetching recipes!';
            } finally {
                this.loading = false;
            }
        },
        newRecipe: {
            name: '',
            description: '',
            ingredients: [],
            show: false,
            submitting: false,
            addIngredient () {
                this.newRecipe.ingredients.push('');
            },
            async submit () {
                if (this.submitting) return;
                
                this.submitting = true;
                try {
                    const response = await fetch('api/recipe', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            name: this.newRecipe.name,
                            description: this.newRecipe.description,
                            ingredients: this.newRecipe.ingredients
                        })
                    });
                    
                    if (!response.ok) {
                        const errorData = await response.json();
                        throw new Error(errorData.error || `Server error: ${response.status}`);
                    }
                    
                    const newRecipe = await response.json();
                    
                    this.recipes.unshift(this.createRecipeComponentFromData(newRecipe));
                    this.error = null;
                    
                    this.newRecipe.name = '';
                    this.newRecipe.description = '';
                    this.newRecipe.ingredients = [];
                } catch (error) {
                    console.error('Error creating recipe:', error);
                    this.error = error.message || 'Error creating recipe!';
                } finally {
                    this.submitting = false;
                }
            },
        },
        startEditingExistingRecipe (recipe) {
            recipe.editingData = JSON.parse(JSON.stringify(recipe.data));
            recipe.editing = true;
        },
        addIngredientToExistingRecipe (recipe) {
            recipe.editingData.ingredients.push({id: null, name: ''});
        },
        async saveExistingRecipe (recipe) {
            try {
                const response = await fetch(`api/recipe/${recipe.editingData.id}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        name: recipe.editingData.name,
                        description: recipe.editingData.description,
                        ingredients: recipe.editingData.ingredients
                    })
                });
                
                const data = await response.json();
                
                if (!response.ok) {
                    throw new Error(data.error || `Server error: ${response.status}`);
                }
                
                recipe.data = data;
                recipe.editing = false;
                recipe.error = null;
            } catch (error) {
                console.error(`Error saving recipe: ${error}`);
                recipe.error = error.message || 'Error saving recipe!';
            }
        },
    };
}
