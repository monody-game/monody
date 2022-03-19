# Monody

Monody is an online werewolf game.

## Events :

- game. +
  - created : home event, triggered to update the game list
  - start : start the game
  - newDay : switch the nigth to the day and reverse => response to each counter end cycle
  - assign : when roles are assigned
  - role-assign : to the player with his assigned role
  - delete : home event, triggered to update the game list  
    
- counter. +
  - end : end the counter

- chat. +
  - send : when a message is sent
