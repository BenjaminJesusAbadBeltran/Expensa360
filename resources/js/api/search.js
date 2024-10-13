import request from '@/utils/request';

export function userSearch(nombre) {
  return request({
    url: '/search/user',
    method: 'get',
    params: { nombre },
  });
}
