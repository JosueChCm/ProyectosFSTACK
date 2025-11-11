const mongoose = require("mongoose");
const {
  DB_USER,
  DB_PASSWORD,
  DB_HOST,
  IP_SERVER,
  API_VERSION,
} = require("./constante");

// Usamos una función asíncrona para conectar con Mongoose
const connectDB = async () => {
  try {
    await mongoose.connect(`mongodb+srv://${DB_USER}:${DB_PASSWORD}${DB_HOST}/`, {
      useNewUrlParser: true,
      useUnifiedTopology: true,
    });
    console.log("✅ Conexión exitosa a la base de datos MongoDB");
  } catch (error) {
    console.error("❌ Error al conectar con MongoDB:", error);
  }
};

// Llamamos la función
connectDB();
