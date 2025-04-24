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
            addIngredient () {
                this.newRecipe.ingredients.push('');
            },
            async submit () {
                try {
                    const newRecipe = await apiRequest('api/recipe/new?name=' + encodeURIComponent(this.newRecipe.name) + '&description=' + encodeURIComponent(this.newRecipe.description) + '&ingredients=' + encodeURIComponent(JSON.stringify(this.newRecipe.ingredients)));
                    this.recipes.unshift(this.createRecipeComponentFromData(newRecipe));
                    this.error = null;
                } catch (error) {
                    /*  Updating the Error message to more descriptive but a better solution would be to:
                     1. Change the endpoint to use POST instead of GET
                     2. Send data as JSON in the request body
                     3. Implement validation and error handling on the backend */
                    
                    this.error = 'Error! Please Add a Name and Description.';
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
                const editedRecipe = JSON.parse(JSON.stringify(recipe.editingData));

                recipe.data = editedRecipe;
                recipe.editing = false;
                recipe.error = null;
            } catch (error) {
                recipe.error = 'Error saving recipe!';
            }
        },
    };
}
