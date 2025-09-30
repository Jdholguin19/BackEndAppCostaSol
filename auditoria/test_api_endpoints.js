const axios = require('axios');

const BASE_URL = 'http://localhost:5000/api';

// Configurar timeout global
axios.defaults.timeout = 10000; // 10 segundos

async function testAPIEndpoints() {
  try {
    console.log('🔐 Iniciando sesión...');
    
    // Login para obtener token válido
    const loginResponse = await axios.post(`${BASE_URL}/auth/login`, {
      email: 'admin@traeia.com',
      password: 'password'
    }).catch(error => {
      console.error('❌ Error en request de login:', error.response?.data || error.message);
      throw error;
    });

    if (!loginResponse.data.success) {
      console.error('❌ Error en login:', loginResponse.data.message);
      return;
    }

    const token = loginResponse.data.data?.token;
    console.log('✅ Login exitoso');
    console.log('Token recibido:', token);
    
    if (!token) {
      console.error('❌ No se recibió token válido');
      return;
    }

    const headers = {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    };

    // Lista de endpoints a probar
    const endpoints = [
      { method: 'GET', url: '/projects/1', name: 'GET /api/projects/1' },
      { method: 'GET', url: '/project-teams/1/members', name: 'GET /api/project-teams/1/members' },
      { method: 'GET', url: '/project-budget/categories', name: 'GET /api/project-budget/categories' },
      { method: 'GET', url: '/project-teams/skills', name: 'GET /api/project-teams/skills' },
      { method: 'GET', url: '/project-teams/roles', name: 'GET /api/project-teams/roles' }
    ];

    console.log('\n📋 Probando endpoints...');

    // Probar cada endpoint
    for (const endpoint of endpoints) {
      try {
        console.log(`🔍 Probando ${endpoint.name}...`);
        
        const response = await axios({
          method: endpoint.method,
          url: `${BASE_URL}${endpoint.url}`,
          headers,
          timeout: 5000 // 5 segundos por request
        });

        console.log(`✅ ${endpoint.name} - Status: ${response.status}`);
        if (response.data?.data) {
          console.log(`   Datos recibidos: ${Array.isArray(response.data.data) ? response.data.data.length + ' elementos' : 'objeto'}`);
        }
      } catch (error) {
        if (error.code === 'ECONNABORTED') {
          console.log(`⏰ ${endpoint.name} - Timeout (5s)`);
        } else if (error.response) {
          console.log(`❌ ${endpoint.name} - Error: ${error.response.data?.message || error.response.statusText}`);
        } else {
          console.log(`❌ ${endpoint.name} - Error de conexión: ${error.message}`);
        }
      }
    }

    console.log('\n🎉 Pruebas completadas');

  } catch (error) {
    console.error('❌ Error general:', error.message);
  }
}

// Ejecutar las pruebas
testAPIEndpoints();