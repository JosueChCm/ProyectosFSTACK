const User = require("../models/user");
const bcrypt = require("bcryptjs");

async function getUser(req, res) {
  try {
    const user = await User.findById(req.user).select("-password");
    return res.status(200).send(user);
  } catch (error) {
    return res.status(500).send({ msg: "Error del servidor" });
  }
}

async function updateUser(req, res) {
  try {
    const { firstname, lastname, email, password } = req.body;

    const user = await User.findById(req.user);

    if (!user) return res.status(404).send({ msg: "Usuario no encontrado" });

    // Validar email único
    if (email && email !== user.email) {
      const exists = await User.findOne({ email });
      if (exists)
        return res.status(400).send({ msg: "El email ya está en uso" });

      user.email = email.toLowerCase();
    }

    if (firstname) user.firstname = firstname;
    if (lastname) user.lastname = lastname;

    if (password) {
      const salt = bcrypt.genSaltSync(10);
      user.password = bcrypt.hashSync(password, salt);
    }

    await user.save();

    return res.status(200).send({ msg: "Usuario actualizado" });
  } catch (error) {
    return res.status(500).send({ msg: "Error del servidor" });
  }
}

module.exports = {
  getUser,
  updateUser,
};
