export default function auth({ next, router }) {
  if (!localStorage.getItem('access-token')) {
    return router.push({ name: 'login' });
  } else if (!sessionStorage.getItem('access-token')) {
    return router.push({ name: 'login' });
  }

  return next();
}
