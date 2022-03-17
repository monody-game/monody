module.exports = class RoleManager {
  static assign(roles, members) {
    const assigned = {};

    for (const role in roles) {
      const member = members[Math.floor(Math.random() * members.length)].user_id
      if (assigned.hasOwnProperty(member)) continue;
      if(roles[role] > 1) {
        for (const spares in roles[role]) {
          assigned[member] = role;
        }
      }
      assigned[member] = role;
    }

    return assigned;
  }
}
