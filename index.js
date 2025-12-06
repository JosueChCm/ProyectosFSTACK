const mongoose = require("mongoose");
const app = require("./app");

const {
  DB_USER,
  DB_PASSWORD,
  DB_HOST,
  IP_SERVER,
  API_VERSION,
} = require("./constante");

const PORT = process.env.POST || 3977;

const MONGO_URI = `mongodb+srv://${DB_USER}:${DB_PASSWORD}${DB_HOST}/`;

mongoose.connect(MONGO_URI)
  .then(() => {
    console.log("✅ Conexión exitosa a la base de datos MongoDB");
    app.listen(PORT, () =>{
    console.log("########################################");
    console.log("######### API REST - con EXPRESS########");
    console.log("########################################");
    console.log(`http://${IP_SERVER}:${PORT}/api/${API_VERSION}/`);
    })
  })
.catch(err => console.error("❌ Error al conectar con MongoDB:", err));

