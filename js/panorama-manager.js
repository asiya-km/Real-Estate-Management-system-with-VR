// Panorama Scene Manager for Property Admin
document.addEventListener('DOMContentLoaded', function() {
    // Scene counter for unique IDs
    let sceneCounter = 0;
    
    // Container for scenes
    const scenesContainer = document.getElementById('panorama-scenes');
    
    // Hidden input to store JSON configuration
    const configInput = document.getElementById('tour-config-json');
    
    // Add scene button
    const addSceneBtn = document.getElementById('add-scene');
    
    // Load existing configuration if available
    let tourConfig = { scenes: [] };
    try {
        if (configInput.value) {
            tourConfig = JSON.parse(configInput.value);
            
            // Render existing scenes
            tourConfig.scenes.forEach(scene => {
                renderSceneCard(scene);
                sceneCounter = Math.max(sceneCounter, parseInt(scene.id.replace('scene', '')) + 1;
            });
        }
    } catch (e) {
        console.error('Error parsing tour configuration:', e);
    }
    
    // Add scene event
    addSceneBtn.addEventListener('click', function() {
        const sceneId = 'scene' + sceneCounter++;
        const newScene = {
            id: sceneId,
            title: 'New Scene',
            panorama: '',
            hotspots: []
        };
        
        tourConfig.scenes.push(newScene);
        renderSceneCard(newScene);
        updateConfigInput();
    });
    
    // Function to render a scene card
    function renderSceneCard(scene) {
        const sceneCard = document.createElement('div');
        sceneCard.className = 'card mb-3 scene-card';
        sceneCard.dataset.sceneId = scene.id;
        
        sceneCard.innerHTML = `
            <div class="card-header d-flex justify-content-between align-items-center">
                <input type="text" class="form-control scene-title" value="${scene.title}" placeholder="Scene Title">
                <div>
                    <button type="button" class="btn btn-sm btn-danger delete-scene">Delete</button>
                </div>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label>Panorama Image</label>
                    <div class="input-group">
                        <input type="file" class="form-control panorama-file" accept="image/*">
                        ${scene.panorama ? `<div class="input-group-append">
                            <span class="input-group-text">${scene.panorama}</span>
                        </div>` : ''}
                    </div>
                </div>
                
                <div class="form-group mt-3">
                    <label>Hotspots</label>
                    <div class="hotspots-container">
                        ${scene.hotspots.map((hotspot, index) => renderHotspot(hotspot, index)).join('')}
                    </div>
                    <button type="button" class="btn btn-sm btn-info mt-2 add-hotspot">Add Hotspot</button>
                </div>
            </div>
        `;
        
        // Add event listeners
        sceneCard.querySelector('.scene-title').addEventListener('change', function() {
            updateSceneProperty(scene.id, 'title', this.value);
        });
        
        sceneCard.querySelector('.delete-scene').addEventListener('click', function() {
            if (confirm('Are you sure you want to delete this scene?')) {
                deleteScene(scene.id);
                sceneCard.remove();
            }
        });
        
        sceneCard.querySelector('.add-hotspot').addEventListener('click', function() {
            addHotspot(scene.id, sceneCard);
        });
        
        sceneCard.querySelector('.panorama-file').addEventListener('change', function(e) {
            if (e.target.files.length > 0) {
                // Here you would typically upload the file to the server
                // For now, we'll just update the scene property with the filename
                updateSceneProperty(scene.id, 'panorama', e.target.files[0].name);
                
                // Add a visual indicator that the file is selected
                const fileNameSpan = document.createElement('span');
                fileNameSpan.className = 'input-group-text';
                fileNameSpan.textContent = e.target.files[0].name;
                
                const inputGroup = this.parentElement;
                const existingAppend = inputGroup.querySelector('.input-group-append');
                if (existingAppend) {
                    existingAppend.remove();
                }
                
                const appendDiv = document.createElement('div');
                appendDiv.className = 'input-group-append';
                appendDiv.appendChild(fileNameSpan);
                inputGroup.appendChild(appendDiv);
            }
        });
        
        // Add hotspot event listeners
        sceneCard.querySelectorAll('.hotspot-card').forEach((hotspotCard, index) => {
            setupHotspotEventListeners(hotspotCard, scene.id, index);
        });
        
        scenesContainer.appendChild(sceneCard);
    }
    
    // Function to render a hotspot
    function renderHotspot(hotspot, index) {
        return `
            <div class="card mb-2 hotspot-card" data-index="${index}">
                <div class="card-body">
                    <div class="form-group">
                        <label>Type</label>
                        <select class="form-control hotspot-type">
                            <option value="info" ${hotspot.type === 'info' ? 'selected' : ''}>Information</option>
                            <option value="scene" ${hotspot.type === 'scene' ? 'selected' : ''}>Scene Link</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Position</label>
                        <div class="row">
                            <div class="col">
                                <label>Pitch</label>
                                <input type="number" class="form-control hotspot-pitch" value="${hotspot.pitch || 0}" step="1">
                            </div>
                            <div class="col">
                                <label>Yaw</label>
                                <input type="number" class="form-control hotspot-yaw" value="${hotspot.yaw || 0}" step="1">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Text</label>
                        <input type="text" class="form-control hotspot-text" value="${hotspot.text || ''}">
                    </div>
                    
                    ${hotspot.type === 'info' ? `
                        <div class="form-group hotspot-info-fields">
                            <label>Description</label>
                            <textarea class="form-control hotspot-description">${hotspot.description || ''}</textarea>
                        </div>
                    ` : ''}
                    
                    ${hotspot.type === 'scene' ? `
                        <div class="form-group hotspot-scene-fields">
                            <label>Target Scene</label>
                            <select class="form-control hotspot-scene-id">
                                <option value="">Select a scene</option>
                                ${tourConfig.scenes.map(s => `
                                    <option value="${s.id}" ${hotspot.sceneId === s.id ? 'selected' : ''}>${s.title}</option>
                                `).join('')}
                            </select>
                        </div>
                    ` : ''}
                    
                    <button type="button" class="btn btn-sm btn-danger delete-hotspot">Delete Hotspot</button>
                </div>
            </div>
        `;
    }
    
    // Setup hotspot event listeners
    function setupHotspotEventListeners(hotspotCard, sceneId, index) {
        const typeSelect = hotspotCard.querySelector('.hotspot-type');
        
        // Type change event
        typeSelect.addEventListener('change', function() {
            const type = this.value;
            updateHotspotProperty(sceneId, index, 'type', type);
            
            // Toggle fields based on type
            const infoFields = hotspotCard.querySelector('.hotspot-info-fields');
            const sceneFields = hotspotCard.querySelector('.hotspot-scene-fields');
            
            if (type === 'info') {
                if (!infoFields) {
                    const infoDiv = document.createElement('div');
                    infoDiv.className = 'form-group hotspot-info-fields';
                    infoDiv.innerHTML = `
                        <label>Description</label>
                        <textarea class="form-control hotspot-description"></textarea>
                    `;
                    
                    // Add event listener to the new description field
                    const descField = infoDiv.querySelector('.hotspot-description');
                    descField.addEventListener('change', function() {
                        updateHotspotProperty(sceneId, index, 'description', this.value);
                    });
                    
                    // Insert before the delete button
                    const deleteBtn = hotspotCard.querySelector('.delete-hotspot');
                    deleteBtn.parentNode.insertBefore(infoDiv, deleteBtn);
                }
                
                if (sceneFields) {
                    sceneFields.remove();
                }
            } else if (type === 'scene') {
                if (!sceneFields) {
                    const sceneDiv = document.createElement('div');
                    sceneDiv.className = 'form-group hotspot-scene-fields';
                    sceneDiv.innerHTML = `
                        <label>Target Scene</label>
                        <select class="form-control hotspot-scene-id">
                            <option value="">Select a scene</option>
                            ${tourConfig.scenes.map(s => `
                                <option value="${s.id}">${s.title}</option>
                            `).join('')}
                        </select>
                    `;
                    
                    // Add event listener to the new scene selector
                    const sceneSelect = sceneDiv.querySelector('.hotspot-scene-id');
                    sceneSelect.addEventListener('change', function() {
                        updateHotspotProperty(sceneId, index, 'sceneId', this.value);
                    });
                    
                    // Insert before the delete button
                    const deleteBtn = hotspotCard.querySelector('.delete-hotspot');
                    deleteBtn.parentNode.insertBefore(sceneDiv, deleteBtn);
                }
                
                if (infoFields) {
                    infoFields.remove();
                }
            }
        });
        
        // Other field change events
        hotspotCard.querySelector('.hotspot-pitch').addEventListener('change', function() {
            updateHotspotProperty(sceneId, index, 'pitch', parseFloat(this.value));
        });
        
        hotspotCard.querySelector('.hotspot-yaw').addEventListener('change', function() {
            updateHotspotProperty(sceneId, index, 'yaw', parseFloat(this.value));
        });
        
        hotspotCard.querySelector('.hotspot-text').addEventListener('change', function() {
            updateHotspotProperty(sceneId, index, 'text', this.value);
        });
        
        // Description field (if present)
        const descField = hotspotCard.querySelector('.hotspot-description');
        if (descField) {
            descField.addEventListener('change', function() {
                updateHotspotProperty(sceneId, index, 'description', this.value);
            });
        }
        
        // Scene ID field (if present)
        const sceneIdField = hotspotCard.querySelector('.hotspot-scene-id');
        if (sceneIdField) {
            sceneIdField.addEventListener('change', function() {
                updateHotspotProperty(sceneId, index, 'sceneId', this.value);
            });
        }
        
        // Delete hotspot button
        hotspotCard.querySelector('.delete-hotspot').addEventListener('click', function() {
            if (confirm('Are you sure you want to delete this hotspot?')) {
                deleteHotspot(sceneId, index);
                hotspotCard.remove();
                
                // Update indices of remaining hotspots
                const sceneCard = document.querySelector(`.scene-card[data-scene-id="${sceneId}"]`);
                const remainingHotspots = sceneCard.querySelectorAll('.hotspot-card');
                remainingHotspots.forEach((card, newIndex) => {
                    card.dataset.index = newIndex;
                });
            }
        });
    }
    
    // Function to add a new hotspot
    function addHotspot(sceneId, sceneCard) {
        const scene = tourConfig.scenes.find(s => s.id === sceneId);
        if (!scene) return;
        
        // Create new hotspot object
        const newHotspot = {
            type: 'info',
            pitch: 0,
            yaw: 0,
            text: 'New Hotspot'
        };
        
        // Add to scene's hotspots
        if (!scene.hotspots) {
            scene.hotspots = [];
        }
        
        const hotspotIndex = scene.hotspots.length;
        scene.hotspots.push(newHotspot);
        
        // Render the new hotspot
        const hotspotsContainer = sceneCard.querySelector('.hotspots-container');
        const hotspotHtml = renderHotspot(newHotspot, hotspotIndex);
        
        // Create a temporary container to convert HTML string to DOM element
        const tempContainer = document.createElement('div');
        tempContainer.innerHTML = hotspotHtml;
        const hotspotCard = tempContainer.firstElementChild;
        
        // Add event listeners to the new hotspot
        setupHotspotEventListeners(hotspotCard, sceneId, hotspotIndex);
        
        // Add to the DOM
        hotspotsContainer.appendChild(hotspotCard);
        
        // Update the configuration
        updateConfigInput();
    }
    
    // Function to update a scene property
    function updateSceneProperty(sceneId, property, value) {
        const scene = tourConfig.scenes.find(s => s.id === sceneId);
        if (scene) {
            scene[property] = value;
            updateConfigInput();
        }
    }
    
    // Function to update a hotspot property
    function updateHotspotProperty(sceneId, hotspotIndex, property, value) {
        const scene = tourConfig.scenes.find(s => s.id === sceneId);
        if (scene && scene.hotspots && scene.hotspots[hotspotIndex]) {
            scene.hotspots[hotspotIndex][property] = value;
            updateConfigInput();
        }
    }
    
    // Function to delete a scene
    function deleteScene(sceneId) {
        tourConfig.scenes = tourConfig.scenes.filter(s => s.id !== sceneId);
        updateConfigInput();
    }
    
    // Function to delete a hotspot
    function deleteHotspot(sceneId, hotspotIndex) {
        const scene = tourConfig.scenes.find(s => s.id === sceneId);
        if (scene && scene.hotspots) {
            scene.hotspots.splice(hotspotIndex, 1);
            updateConfigInput();
        }
    }
    
    // Function to update the hidden input with the current configuration
    function updateConfigInput() {
        configInput.value = JSON.stringify(tourConfig);
    }
});