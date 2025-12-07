const jwt = require("../utils/jwt");
const { JWT_SECRET_KEY } = require("../constante");

function verifyToken(req, res, next) {
  const header = req.headers.authorization;
  if (!header) return res.status(403).send({ msg: "Token requerido" });

  const token = header.replace("Bearer ", "");

  try {
    const decoded = jwt.decoded(token);

    if (!decoded || Date.now() > decoded.exp) {
      return res.status(401).send({ msg: "Token expirado o inválido" });
    }

    req.user = decoded.user_id;

    next();
  } catch (error) {
    return res.status(401).send({ msg: "Token inválido" });
  }
}

module.exports = verifyToken;
