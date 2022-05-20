# Monody

Monody is an online werewolf game.

## Events :

🏠 : Home channel event

🔴 : Private game event (per client)

🟡 : Semi-private game event (per roles)

🟢 : Public game event

- game. +
    - created : triggered to update the game list 🏠
    - delete : triggered to update the game list 🏠
    - role-assign : to the player with his assigned role 🔴
    - state : Update the current game state. Used to switch between rounds or to start game 🟢

- chat. +
    - send : when a message is sent 🟢
    - chat.werewolf : message from the private werewolves chat 🟡
