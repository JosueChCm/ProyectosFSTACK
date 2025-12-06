const bcrypt = require(`bcryptjs`);
const User = require(`../models/user`);
// Para login
const jwt = require(`../utils/jwt`);

//para token
const {JWT_SECRET_KEY} = require (`../constante`);

// ------------------------------------------------------
// Registro
// ------------------------------------------------------     
async function register(req, res) {
  try {
    const { firstname, lastname, email, password } = req.body;

    if (!email) {
      return res.status(400).send({ msg: "El email es obligatorio" });
    }
    if (!password) {
      return res.status(400).send({ msg: "La contraseña es obligatoria" });
    }

    const emailLowerCase = email.toLowerCase();

    // Crear nuevo usuario
    const user = new User({
      firstname,
      lastname,
      email: emailLowerCase,
      password,
      role: "user",
      active: false,
    });

    // Encriptar contraseña
    const salt = bcrypt.genSaltSync(10);
    const hashPassword = bcrypt.hashSync(password, salt);
    user.password = hashPassword;

    const userStore = await user.save();

    return res.status(201).send({
      msg: "Usuario creado correctamente",
      user: userStore,
    });
  } catch (error) {
    console.error("Error en register:", error);
    return res
      .status(500)
      .send({ msg: "Error del servidor", error: error.message });
  }
}

// ------------------------------------------------------
// Login
// ------------------------------------------------------
async function login(req, res) {
  try {
    const { email, password } = req.body;

    // ⚙ Validar campos requeridos
    if (!email || !password) {
      return res.status(400).json({ msg: "El email y la contraseña son obligatorios" });
    }

    const emailLowerCase = email.toLowerCase();

    // ⚙ Buscar usuario
    const userStore = await User.findOne({ email: emailLowerCase });

    if (!userStore) {
      return res.status(404).json({ msg: "El usuario no existe" });
    }

    // ⚙ Comparar contraseña
    const passwordMatch = await bcrypt.compare(password, userStore.password);
    if (!passwordMatch) {
      return res.status(400).json({ msg: "Contraseña incorrecta" });
    }

    // ⚙ Verificar si el usuario está activo
    if (!userStore.active) {
      return res.status(401).json({ msg: "Usuario no autorizado o inactivo" });
    }

    // ⚙ Generar tokens
    const accessToken = jwt.createAccessToken(userStore);
    const refreshToken = jwt.createRefreshToken(userStore);

    return res.status(200).json({
      msg: "Inicio de sesión exitoso",
      access: accessToken,
      refresh: refreshToken,
    });
  } catch (error) {
    console.error("Error en login:", error);
    return res.status(500).json({ msg: "Error del servidor", error: error.message });
  }
}
// Función accessToken
async function refreshAccessToken(req, res) {
  try {
    const { token } = req.body;

    if (!token) {
      return res.status(400).send({ msg: "Token requerido" });
    }

    // Decodificar token
    let payload;
    try {
      payload = jwt.decoded(token);
    } catch (error) {
      return res.status(400).send({ msg: "Token inválido o expirado" });
    }

    const { user_id } = payload;

    // Buscar usuario
    const userStorage = await User.findById(user_id);

    if (!userStorage) {
      return res.status(404).send({ msg: "Usuario no encontrado" });
    }

    // Crear nuevo access token
    const accessToken = jwt.createAccessToken(userStorage);

    return res.status(200).send({ accessToken });
  } catch (error) {
    console.error("Error en refreshAccessToken:", error);
    return res.status(500).send({ msg: "Error del servidor" });
  }
}

module.exports = {
  register,
  login,
  refreshAccessToken
};

