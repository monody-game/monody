module.exports = class RoleManager {
  static assign(roles, members) {
    const assigned = {};

    for (const role in roles) {
      let member = members[Math.floor(Math.random() * members.length)].user_id
      while (assigned.hasOwnProperty(member)) {
        member = members[Math.floor(Math.random() * members.length)].user_id
      }
      if(roles[role] > 1) {
        for (const spares in roles[role]) {
          assigned[member] = parseInt(role);
        }
      }
      assigned[member] = parseInt(role);
    }

    return assigned;
  }
}
