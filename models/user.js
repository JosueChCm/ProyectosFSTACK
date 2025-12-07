// models/user.js
const mongoose = require("mongoose");

const UserSchema = mongoose.Schema({
  firstname: String,
  lastname: String,
  email: {
    type: String,
    unique: true,
    required: true
  },

  role: String,
  active: Boolean,
  password: String,
});

module.exports = mongoose.model("User", UserSchema);
