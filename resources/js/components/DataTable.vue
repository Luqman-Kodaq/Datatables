<template>
    <div>
        <div class="card card-default">
            <div class="card-header">
                <div class="container">
                    <div class="row">
                        <div class="col-md-6 d-flex">
                            <h2>{{ response.table }}</h2>
                        </div>
                        <div class="col-md-6 d-flex">
                            <a href="#" class="btn btn-outline-secondary ml-auto px-auto" v-if="response.allow.creation" @click.prevent="creating.active = !creating.active">
                                {{ creating.active ? 'Cancel' : 'Add Record' }}
                            </a>
                        </div>
                    </div>
                </div>
                
            </div>

            <div class="card-body">
                <div class="card" v-if="creating.active">
                    <div class="card-body">
                        <form action="#" class="form-horizontal" @submit.prevent="store">
                            <div class="form-group" v-for="column in response.updatable" :key="column.id" :class="{ 'has-error': creating.errors[column] }">
                                <label class="col-md-3 control-label" :for="column">{{ response.column_map[column] || column }}</label>
                                <div class="col-md-6">
                                    <input type="text" :id="column" class="form-control" v-model="creating.form[column]">
                                    <span class="help-block" v-if="creating.errors[column]">
                                        <strong class="is-invalid">{{ creating.errors[column][0] }}</strong>
                                    </span>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-md-6">
                                    <button class="btn btn-secondary">
                                        Send
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <form action="#" @submit.prevent="getRecords">
                    <label for="search">Search</label>
                    <div class="row row-fluid">
                        <div class="form-group col-md-3">
                            <select class="form-control" v-model="search.column">
                                <option :value="column" v-for="column in response.displayable" :key="column.id">
                                    {{ column }}
                                </option>
                            </select>
                        </div>
                        <div class="form-group col-md-3">
                            <select class="form-control" v-model="search.operator">
                                <option value="equals">=</option>
                                <option value="contains">contains</option>
                                <option value="starts_with">starts with</option>
                                <option value="ends_with">ends with</option>
                                <option value="greater_than">></option>
                                <option value="less_than"><</option>
                                <option value="greater_than_or_equal_to">>=</option>
                                <option value="less_than_or_equal_to"><=</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <div class="input-group">
                                <input type="text" id="search" class="form-control" v-model="search.value">
                                <span class="input-group-btn">
                                    <button class="btn btn-light" type="submit">Search</button>
                                </span>
                            </div>
                        </div>
                    </div>
                </form>
                <div class="row">
                    <div class="form-group col-md-10">
                        <label for="filter">Quick search current results</label>
                        <input type="text" id="filter" class="form-control" v-model="quickSearchQuery">
                    </div>
                    <div class="form-group col-md-2">
                        <label for="limit"> Display records</label>
                        <select id="limit" class="form-control" v-model="limit" @change="getRecords()">
                            <option value="50">50</option>
                            <option value="100">100</option>
                            <option value="1000">1000</option>
                            <option value="">All</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="card card-default">
            <div class="card-header" v-if="selected.length">
                <a href="#" data-toggle="dropdown" class="dropdown-toggle">With Selected</a>
                <ul class="dropdown-menu">
                    <li class="dropdown-item">
                        <a href="#" @click.prevent="destroy(selected)">Delete</a>
                    </li>
                </ul>
            </div>
            
            <div class="card-body">
            <div class="table-responsive" v-if="filteredRecords.length">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th v-if="canSelectItems"><input type="checkbox" @change="toggleSelectAll" :checked="filteredRecords.length === selected.length"></th>
                            <th v-for="column in response.displayable" v-bind:key="column">
                                <span class="sortable" @click="sortBy(column)">{{ response.column_map[column] || column }}</span>

                                <div class="arrow"
                                    v-if="sort.key === column"
                                    :class="{ 'arrow--asc': sort.order === 'asc', 'arrow--desc': sort.order === 'desc' }"></div>
                            </th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="record in filteredRecords" :key="record.id">
                            <td v-if="canSelectItems">
                                <input type="checkbox" v-model="selected" :value="record.id">
                            </td>
                            <td v-for="(columnValue, column) in record" :key="column">
                                <template v-if="editing.id === record.id && isUpdatatable(column)">
                                    <div class="form-group" :class="{ 'has-error': editing.errors[column] }">
                                        <input type="text" class="form-control" v-model="editing.form[column]">
                                    </div>
                                    <span class="help-block" v-if="editing.errors[column]">
                                        <strong class="is-invalid">{{ editing.errors[column][0] }}</strong>
                                    </span>
                                </template>
                                
                                <template v-else>
                                    {{ columnValue }}
                                </template>
                            </td>
                            <td>
                                <template>
                                    <a href="#" @click.prevent="edit(record)" v-if="editing.id !== record.id"><i class="far fa-edit"></i> Edit</a>
                                    <a href="" @click.prevent="destroy(record.id)" v-if="response.allow.deletion"><i class="fas fa-trash"></i> Trash</a>
                                </template>
                                <template v-if="editing.id === record.id">
                                    <a href="#" @click.prevent="update"><i class="fas fa-check"></i> Save</a> <br/>
                                    <a href="#" @click.prevent="editing.id = null"><i class="fas fa-times"></i> Cancel</a>
                                </template>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p v-else>No results</p>
            </div>
        </div>
    </div>
</template>

<script>
    import queryString from 'query-string';
    export default {
        props: [
            'endpoint'
        ],
        data () {
            return {
                response: {
                    table: '',
                    displayable: [],
                    records: [],
                    allow: {}
                },
                sort: {
                    key: 'id',
                    order: 'asc'
                },
                editing: {
                    id: null,
                    form: {},
                    errors: []
                },
                limit: 50,
                quickSearchQuery: '',
                search: {
                    value: '',
                    operator: 'equals',
                    column: 'id'
                },
                creating: {
                    active: false,
                    form: {},
                    errors: []
                },
                selected: []
            }
        },
        computed: {
            filteredRecords() {
                let data = this.response.records

                data = data.filter((row) => {
                    return Object.keys(row).some((key) => {
                        return String(row[key]).toLowerCase().indexOf(this.quickSearchQuery.toLowerCase()) > -1
                    })
                })

                if(this.sort.key) {
                    data = _.orderBy(data, (i) => {
                        let value = i[this.sort.key];

                    if(!isNaN(parseFloat(value))) {
                        return parseFloat(value);
                    }

                    return String(i[this.sort.key]).toLowerCase()
                    }, this.sort.order)
                }

                return data;
            },
            canSelectItems () {
                return this.filteredRecords.length <= 500
            }
        },
        methods: {
            getRecords() {
                return axios.get(`${this.endpoint}?${this.getQueryParameters()}`).then((response) => {
                    this.response = response.data.data
                })
            },
            getQueryParameters() {
                return queryString.stringify({
                    limit: this.limit,
                    ...this.search
                })
            },
            sortBy(column) {
                this.sort.key = column
                this.sort.order = this.sort.order === 'asc' ? 'desc' : 'asc'
            },
            edit (record) {
                this.editing.errors = []
                this.editing.id = record.id
                this.editing.form = _.pick(record, this.response.updatable)
            },
            toggleSelectAll () {
                if (this.selected.length > 0) {
                    this.selected = []
                    return
                }

                this.selected = _.map(this.filteredRecords, 'id')
            },
            isUpdatatable(column) {
                return this.response.updatable.includes(column)
            },
            update() {
                axios.patch(`${this.endpoint}/${this.editing.id}`, this.editing.form).then(() => {
                    this.getRecords().then(() => {
                        this.editing.id = null
                        this.editing.form = {}
                    })
                }).catch((error) => {
                    if (error.response.status === 422) {
                        this.editing.errors = error.response.data.errors
                    }
                })
            },
            store() {
                axios.post(`${this.endpoint}`, this.creating.form).then(() => {
                    this.getRecords().then(() => {
                        this.creating.active = false
                        this.creating.form = {}
                        this.creating.errors = []
                    });
                }).catch((error) => {
                    if (error.response.status === 422) {
                        this.creating.errors = error.response.data.errors
                    }
                })
            },
            destroy (record) {
                if (!window.confirm(`Are you sure you want to delete this?`)) {
                    return
                }

                axios.delete(`${this.endpoint}/${record}`).then(() => {
                    this.selected = []
                    this.getRecords()

                    if (this.selected.length) {
                        this.toggleSelectAll()
                    }
                })
            },
        },
         mounted() {
            this.getRecords()
        }
    }
</script>

<style lang="scss">
    .sortable {
        cursor: pointer;
    }

    .is-invalid {
        color: red;
    }

    .arrow {
        display: inline-block;
        vertical-align: middle;
        width: 0;
        height: 0;
        margin-left: 5px;
        opacity: .6;

        &--asc {
            border-left: 4px solid transparent;
            border-right: 4px solid transparent;
            border-bottom: 4px solid #222;

        }

        &--desc {
            border-left: 4px solid transparent;
            border-right: 4px solid transparent;
            border-top: 4px solid #222;
        }
    }
</style>
