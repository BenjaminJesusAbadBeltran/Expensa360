import Resource from '@/api/resource';

class ExpensaResource extends Resource {
  constructor() {
    super('expensas');
  }
}

export { ExpensaResource as default };
