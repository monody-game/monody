export default async function exists({next, router, to}) {
  if (!to.params.id) {
    return router.push({name: 'play'});
  }

  const response = await JSONFetch('/game/check', 'POST', {
    game_id: to.params.id,
  });

  if (response.data) {
    return next()
  } else {
    return router.push({name: 'play'});
  }
}
