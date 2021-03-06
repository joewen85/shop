@extends('layouts.base')
@section('title', '编辑优惠券')
@section('content')
<link rel="stylesheet" type="text/css" href="{{static_url('yunshop/goods/vue-goods1.css')}}"/>
    <div class="all">
        <div id="app" v-cloak>
            <div class="vue-crumbs">
                <a @click="goParent">优惠券管理</a> > 编辑优惠券
            </div>
            <el-form ref="form" :model="form" :rules="rules" label-width="15%">
                <div class="vue-head" style="margin-bottom:20px">
                    <div class="vue-main-title">
                        <div class="vue-main-title-left"></div>
                        <div class="vue-main-title-content">基本信息</div>
                    </div>
                    <div class="vue-main-form">
                        <el-form-item label="排序" prop="display_order">
                            <el-input v-model="form.display_order" style="width:70%;"></el-input>
                            <div class="tip">数字越大越靠前</div>
                        </el-form-item>
                        <el-form-item label="优惠券名称" prop="name">
                            <el-input v-model="form.name" style="width:70%;"></el-input>
                        </el-form-item>
                        <el-form-item label="是否开启" prop="status">
                            <el-switch v-model.number="form.status" :active-value="1" :inactive-value="0"></el-switch>
                            <div class="tip">关闭后,用户无法领取, 但是已经被领取的可以继续使用</div>
                        </el-form-item>
                    </div>
                </div>
                <div class="vue-head" style="margin-bottom:20px">
                    <div class="vue-main-title">
                        <div class="vue-main-title-left"></div>
                        <div class="vue-main-title-content">优惠方式</div>
                    </div>
                    <div class="vue-main-form">
                        <el-form-item label="使用条件 - 订单金额" prop="enough">
                            <el-input v-model="form.enough" style="width:70%;"></el-input>
                            <div class="tip">消费满多少金额才可以使用该优惠券 (设置为 0 则不限制消费金额)</div>
                        </el-form-item>
                        <el-form-item label="领取条件 - 会员等级" prop="level_limit">
                            <el-select v-model="form.level_limit" style="width:70%" placeholder="请选择会员" >
                                <el-option :value="-1" label="所有会员"></el-option>
                                <el-option v-for="item in member_list" :key="item.id" :label="item.level_name+'(及以上等级可以领取)'" :value="item.id"></el-option>
                            </el-select>
                            <div class="tip">选择"所有会员"表示商城的所有会员,包括没有划分等级的; <br>例如: 选择等级 3,表示包括 3 以及大于等级 3 的会员都可领取,即等级 3, 4, 5...都可以领取.</div>
                        </el-form-item>
                        <el-form-item label="使用时间限制" prop="time_limit">
                            <el-radio v-model="form.time_limit" :label="0">
                                获取后<el-input v-model.number="form.time_days" style="width:100px;" size="mini" :disabled="form.time_limit==1"></el-input>天内有效(0 为不限时间使用)
                            </el-radio><br>
                            <el-radio v-model.number="form.time_limit" :label="1">
                                时间范围
                            </el-radio>
                            <el-date-picker
                                :disabled="form.time_limit==0"
                                size="mini"
                                v-model="form.time"
                                type="daterange"
                                value-format="timestamp"
                                range-separator="至"
                                start-placeholder="开始日期"
                                end-placeholder="结束日期"
                                style="margin-left:5px;"
                                :default-time="['00:00:00', '23:59:59']"
                                align="right">
                            </el-date-picker>
                        </el-form-item>
                        <el-form-item label="使用方式" prop="is_complex">
                            <el-radio v-model.number="form.is_complex" :label="0">单张使用</el-radio>
                            <el-radio v-model.number="form.is_complex" :label="1">多张一起使用</el-radio>
                            <div class="tip">如选择单张使用，则一笔订单只能使用一张该类型的优惠券； 选择多张一起使用，则满足使用的金额就可以， 比如我有300-50优惠券3张，下单金额满900元，可以用三张，下单金额满600元可以用2张，下单金额满300元可以用一张</div>
                        </el-form-item>
                        <el-form-item label="优惠方式" prop="coupon_method">
                            <el-radio v-model.number="form.coupon_method" :label="1">立减</el-radio>
                            <el-radio v-model.number="form.coupon_method" :label="2">打折</el-radio>
                            <div>
                                <el-input v-model="form.deduct" style="width:70%" v-show="form.coupon_method==1">
                                    <template slot="prepend">立减</template>
                                    <template slot="append">元</template>
                                </el-input>
                                <el-input v-model="form.discount" style="width:70%" v-show="form.coupon_method==2">
                                    <template slot="prepend">打</template>
                                    <template slot="append">折</template>
                                </el-input>
                            </div>
                            
                            <div class="tip">最多保留两位小数</div>
                        </el-form-item>
                        <el-form-item label="适用范围" prop="use_type">
                        <el-radio-group v-model.number="form.use_type">
                            <el-radio :label="0">平台自营商品（不包含供应商商品）</el-radio>
                            <el-radio :label="1">指定商品分类 </el-radio>
                            <el-radio :label="2">指定商品</el-radio>
                            <el-radio :label="4" v-if="store_is_open==1">指定门店</el-radio>
                            <el-radio :label="7" v-if="hotel_is_open==1">指定酒店  </el-radio>
                            <el-radio :label="8">兑换券 </el-radio>
                        </el-radio-group>
                            
                            <!-- 分类 -->
                            <div v-show="form.use_type==1">
                                <el-tag v-for="(tag,index) in category_names" :key="index" closable @close="closeCategory(index)" style="margin-right:5px;">
                                    [[tag]]
                                </el-tag>
                            </div>
                            <!-- 商品 -->
                            <div v-show="form.use_type==2">
                                <el-tag v-for="(tag,index) in goods_names" :key="index" closable @close="closeGoods(index)" style="margin-right:5px;">
                                    [[tag]]
                                </el-tag>
                            </div>
                            <!-- 门店 -->
                            <div v-show="form.use_type==4">
                                <el-tag v-for="(tag,index) in store_names" :key="index" closable @close="closeStore(index)" style="margin-right:5px;">
                                    [[tag]]
                                </el-tag>
                            </div>
                            <!-- 酒店 -->
                            <div v-show="form.use_type==7">
                                <el-tag v-for="(tag,index) in hotel_names" :key="index" closable @close="closeHotel(index)" style="margin-right:5px;">
                                    [[tag]]
                                </el-tag>
                            </div>
                            <!-- 兑换券 -->
                            <div v-show="form.use_type==8">
                                <el-tag v-for="(tag,index) in goods_name" :key="index" closable @close="closeGoods(index)" style="margin-right:5px;">
                                    [[tag]]
                                </el-tag>
                            </div>

                            <div>
                                <el-button size="mini" @click="openDia(1)" v-show="form.use_type==1">选择分类</el-button>
                                <el-button size="mini" @click="openDia(2)" v-show="form.use_type==2">选择商品</el-button>
                                <el-button size="mini" @click="openDia(4)" v-show="form.use_type==4">选择门店</el-button>
                                <el-button size="mini" @click="openDia(7)" v-show="form.use_type==7">选择酒店</el-button>
                                <el-button size="mini" @click="openDia(8)" v-show="form.use_type==8">选择兑换商品</el-button>
                            </div>

                            <div class="tip" v-show="form.use_type==0">如选择此项,则支持商城所有商品使用!</div>
                        </el-form-item>
                        <el-form-item label="是否可领取" prop="get_type">
                            <el-radio v-model.number="form.get_type" :label="1">可以</el-radio>
                            <el-radio v-model.number="form.get_type" :label="0">不可以</el-radio>
                            <div class="tip">是否可以在领券中心领取 (或者只能手动发放)</div>

                            <el-input v-model.number="form.get_max" style="width:70%" v-show="form.get_type==1">
                                <template slot="prepend">每人限领</template>
                                <template slot="append">张</template>
                            </el-input>
                            <div class="tip" v-show="form.get_type==1">每人限领数量 (-1为不限制数量)</div>
                        </el-form-item>

                        <el-form-item label="发放总数" prop="total">
                            <el-input v-model="form.total" style="width:70%;"></el-input>
                            <div class="tip">优惠券总数量，没有则不能领取或发放, -1 为不限制数量</div>
                        </el-form-item>
                    </div>
                </div>
            </el-form>
            <!-- 分页 -->
            <div class="vue-page">
                <div class="vue-center">
                    <el-button type="primary" @click="submitForm('form')">提交</el-button>
                    <el-button @click="goBack">返回</el-button>
                </div>
            </div>
            <upload-img :upload-show="uploadShow" :name="chooseImgName" @replace="changeProp" @sure="sureImg"></upload-img>

            
            <el-dialog :visible.sync="category_show" width="60%" center title="选择分类">
                <div>
                    <div>
                        <el-input v-model="category_keyword" style="width:70%"></el-input>
                        <el-button type="primary" @click="searchCategory()">搜索</el-button>
                    </div>
                    <el-table :data="category_list" style="width: 100%;height:500px;overflow:auto" v-loading="loading">
                        <el-table-column label="ID" prop="id" align="center" width="100px"></el-table-column>
                        <el-table-column label="分类名称">
                            <template slot-scope="scope">
                                <div v-if="scope.row" style="display:flex;align-items: center">
                                    <div style="margin-left:10px">[[scope.row.name]]</div>
                                </div>
                            </template>
                        </el-table-column>
                        
                        <el-table-column prop="refund_time" label="操作" align="center" width="320">
                            <template slot-scope="scope">
                                <el-button @click="chooseCategory(scope.row)">
                                    选择
                                </el-button>
                                
                            </template>
                        </el-table-column>
                    </el-table>
                </div>
                <span slot="footer" class="dialog-footer">
                    <el-button @click="category_show = false">取 消</el-button>
                </span>
            </el-dialog>
            <el-dialog :visible.sync="goods_show" width="60%" center title="选择商品">
                <div>
                    <div>
                        <el-input v-model="goods_keyword" style="width:70%"></el-input>
                        <el-button type="primary" @click="searchGoods()">搜索</el-button>
                    </div>
                    <el-table :data="goods_list" style="width: 100%;height:500px;overflow:auto" v-loading="loading">
                        <el-table-column label="ID" prop="id" align="center" width="100px"></el-table-column>
                        <el-table-column label="商品信息">
                            <template slot-scope="scope">
                                <div v-if="scope.row" style="display:flex;align-items: center">
                                    <div style="margin-left:10px">[[scope.row.title]]</div>
                                </div>
                            </template>
                        </el-table-column>
                        
                        <el-table-column prop="refund_time" label="操作" align="center" width="320">
                            <template slot-scope="scope">
                                <el-button @click="chooseGoods(scope.row)">
                                    选择
                                </el-button>
                                
                            </template>
                        </el-table-column>
                    </el-table>
                </div>
                <el-row style="background-color:#fff;">
                <el-col :span="24" align="center" migra style="padding:15px 5% 15px 0" v-loading="loading">
                        <el-pagination background  @current-change="currentChange" 
                            :current-page="current_page"
                            layout="prev, pager, next"
                            :page-size="Number(page_size)" :current-page="current_page" :total="page_total"></el-pagination>
                    </el-col>
                </el-row>
                <span slot="footer" class="dialog-footer">
                    <el-button @click="goods_show = false">取 消</el-button>
                </span>
            </el-dialog>
            <el-dialog :visible.sync="store_show" width="60%" center title="选择门店">
                <div>
                    <div>
                        <el-input v-model="store_keyword" style="width:70%"></el-input>
                        <el-button type="primary" @click="searchStore()">搜索</el-button>
                    </div>
                    <el-table :data="store_list" style="width: 100%;height:500px;overflow:auto" v-loading="loading">
                        <el-table-column label="ID" prop="id" align="center" width="100px"></el-table-column>
                        <el-table-column label="门店名称">
                            <template slot-scope="scope">
                                <div v-if="scope.row" style="display:flex;align-items: center">
                                    <div style="margin-left:10px">[[scope.row.store_name]]</div>
                                </div>
                            </template>
                        </el-table-column>
                        
                        <el-table-column prop="refund_time" label="操作" align="center" width="320">
                            <template slot-scope="scope">
                                <el-button @click="chooseStore(scope.row)">
                                    选择
                                </el-button>
                                
                            </template>
                        </el-table-column>
                    </el-table>
                </div>
                <span slot="footer" class="dialog-footer">
                    <el-button @click="store_show = false">取 消</el-button>
                </span>
            </el-dialog>
            <el-dialog :visible.sync="hotel_show" width="60%" center title="选择酒店">
                <div>
                    <div>
                        <el-input v-model="hotel_keyword" style="width:70%"></el-input>
                        <el-button type="primary" @click="searchHotel()">搜索</el-button>
                    </div>
                    <el-table :data="hotel_list" style="width: 100%;height:500px;overflow:auto" v-loading="loading">
                        <el-table-column label="ID" prop="id" align="center" width="100px"></el-table-column>
                        <el-table-column label="酒店名称">
                            <template slot-scope="scope">
                                <div v-if="scope.row" style="display:flex;align-items: center">
                                    <div style="margin-left:10px">[[scope.row.hotel_name]]</div>
                                </div>
                            </template>
                        </el-table-column>
                        
                        <el-table-column prop="refund_time" label="操作" align="center" width="320">
                            <template slot-scope="scope">
                                <el-button @click="chooseHotel(scope.row)">
                                    选择
                                </el-button>
                                
                            </template>
                        </el-table-column>
                    </el-table>
                </div>
                <span slot="footer" class="dialog-footer">
                    <el-button @click="hotel_show = false">取 消</el-button>
                </span>
            </el-dialog>
        </div>
    </div>
    @include('public.admin.uploadImg')  
    <script>
        const category_url = '{!! yzWebFullUrl('goods.category.get-search-categorys-json') !!}';
        const goods_url = '{!! yzWebFullUrl('goods.goods.get-search-goods-json') !!}';
        const store_url = '{!! yzWebFullUrl('goods.goods.get-search-store-json') !!}';
        const hotel_url = '{!! yzWebFullUrl('goods.goods.get-search-hotel-json') !!}';
        var app = new Vue({
            el:"#app",
            delimiters: ['[[', ']]'],
            name: 'test',
            data() {
                let id = {!! $id?:0 !!};
                console.log(id);
                return{
                    loading:false,
                    search_loading:false,
                    all_loading:false,
                    page_total:0,
                    page_size:0,
                    current_page:0,
                    id:id,
                    hotel_is_open:0,
                    store_is_open:0,
                    form:{
                        display_order:'0',
                        name:'',
                        status:0,
                        use_type:0,
                        enough:'',
                        level_limit:-1,
                        time_days:'0',
                        time_limit:0,
                        is_complex:0,
                        coupon_method:1,
                        use_type:0,
                        get_type:0,
                        get_max:'1',
                        total:'1',
                        discount:'0',
                        deduct:'0',
                        time:[],
                    },

                    category_ids : [],
                    category_names:[],
                    goods_ids:[],	
                    goods_names:[],
                    store_ids:[],
                    store_names:[],
                    goods_id:[],
                    goods_name:[],
                    hotel_ids:[],
                    hotel_names:[],
                    member_list:[],
                    goods_list:[],

                    goodsShow:false,
                    chooseGoodsItem:{},//选中的商品
                    // 分类
                    category_show:false,
                    category_list:[],
                    category_keyword:'',
                    // 商品
                    goods_show:false,
                    goods_list:[],
                    goods_keyword:'',
                    // 门店
                    store_show:false,
                    store_list:[],
                    store_keyword:'',
                    // 酒店
                    hotel_show:false,
                    hotel_list:[],
                    hotel_keyword:'',

                    keyword:'',
                    submit_url:'',
                    showVisible:false,

                    uploadShow:false,
                    chooseImgName:'',
                    
                    loading: false,
                    uploadImg1:'',
                    rules:{
                        name:{ required: true, message: '请输入优惠券名称'},
                        enough:{ required: true, message: '请输入使用条件 - 订单金额'},
                    },
                    real_search_form:'',

                }
            },
            created() {


            },
            mounted() {
                if(this.id) {
                    this.submit_url = '{!! yzWebFullUrl('coupon.coupon.edit') !!}';
                }
                else {
                    this.submit_url = '{!! yzWebFullUrl('coupon.coupon.create') !!}';
                }
                this.getData();

            },
            methods: {
                getData() {
                    let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                    this.$http.post(this.submit_url,{id:this.id}).then(function (response) {
                            if (response.data.result){
                                this.hotel_is_open = response.data.data.hotel_is_open;
                                this.store_is_open = response.data.data.store_is_open;
                                this.member_list = response.data.data.memberlevels || [];
                                if(this.id && response.data.data.coupon) {
                                    let coupon = response.data.data.coupon;
                                    this.form.display_order = coupon.display_order;
                                    this.form.name = coupon.name;
                                    this.form.status = coupon.status;
                                    this.form.use_type = coupon.use_type;
                                    this.form.enough = coupon.enough;
                                    this.form.level_limit = coupon.level_limit;
                                    this.form.time_days = coupon.time_days;
                                    this.form.time_limit = coupon.time_limit;
                                    this.form.is_complex = coupon.is_complex;
                                    this.form.coupon_method = coupon.coupon_method;
                                    this.form.use_type = coupon.use_type;
                                    this.form.get_type = coupon.get_type;
                                    this.form.get_max = coupon.get_max;
                                    this.form.total = coupon.total;
                                    this.form.discount = coupon.discount;
                                    this.form.deduct = coupon.deduct;

                                    this.category_ids = response.data.data.category_ids || [];
                                    this.category_names = response.data.data.category_names || [];
                                    
                                    
                                    this.store_ids = response.data.data.store_ids || [];
                                    this.store_names = response.data.data.store_names || [];
                                    if(this.form.use_type==2) {
                                        this.goods_ids = response.data.data.goods_ids || [];	
                                        this.goods_names = response.data.data.goods_names || [];
                                    }
                                    else if(this.form.use_type==8) {
                                        this.goods_id = response.data.data.goods_ids || [];
                                        this.goods_name = response.data.data.goods_names || [];
                                    }
                                    if(this.goods_names) {
                                        this.goods_names.forEach((item,index) => {
                                            if(item) {
                                                this.goods_names[index] = this.escapeHTML(item);
                                            }
                                        })
                                    }
                                    if(this.goods_name) {
                                        this.goods_name.forEach((item,index) => {
                                            if(item) {
                                                this.goods_name[index] = this.escapeHTML(item);
                                            }
                                        })
                                    }
                                    if(response.data.data.hotel_ids) {
                                        response.data.data.hotel_ids.forEach((item,index) => {
                                            this.hotel_names.push(item.hotel_name);
                                            this.hotel_ids.push(item.id);
                                        })
                                    }
                                    if(response.data.data.timeend) {
                                        this.form.time = [];
                                        this.form.time[0] = response.data.data.timestart*1000;
                                        this.form.time[1] = response.data.data.timeend*1000;
                                        console.log(this.form)
                                    }
                                    
                                }
                                if(!response.data.data.timeend) {
                                    this.form.time.push(Math.round(new Date()),Math.round(new Date())+(24*60*60*1000*7))
                                }
                            }
                            else {
                                this.$message({message: response.data.msg,type: 'error'});
                            }
                            loading.close();
                        },function (response) {
                            this.$message({message: response.data.msg,type: 'error'});
                            loading.close();
                        }
                    );
                },
                
                openDia(type) {
                    if(type==1) {
                        this.category_show = true;
                    }
                    else if(type==2) {
                        this.goods_show = true;
                    }
                    else if(type==4) {
                        this.store_show = true;
                    }
                    else if(type==7) {
                        this.hotel_show = true;
                    }
                    else if(type==8) {
                        this.goods_show = true;
                    }
                },
                currentChange(val) {
                    this.loading = true;
                    this.$http.post(goods_url,{page:val,keyword:this.real_search_form}).then(function (response){
                        if (response.data.result){
                              let datas = response.data.data.goods;
                              this.goods_list=datas.data 
                              this.page_total = datas.total;
                              this.page_size = datas.per_page;
                              this.current_page = datas.current_page;
                              this.real_search_form=this.goods_keyword;
                              this.goods_list.forEach((item,index) => {
                                if(item.title) {
                                    item.title = this.escapeHTML(item.title);
                                }
                            });
                              this.loading = false;
                        } else {
                            this.$message({message: response.data.msg,type: 'error'});
                        }
                    },function (response) {
                        console.log(response);
                        this.loading = false;
                    }
                    );
                },
                searchGoods() {
                    let that = this;
                    this.loading = true;
                    this.$http.post(goods_url,{keyword:this.goods_keyword}).then(response => {
                        if (response.data.result) {
                            let datas = response.data.data.goods;
                              this.goods_list=datas.data 
                              this.page_total = datas.total;
                              this.page_size = datas.per_page;
                              this.current_page = datas.current_page;
                            this.goods_list.forEach((item,index) => {
                                if(item.title) {
                                    item.title = this.escapeHTML(item.title);
                                }
                            });
                        } else {
                            this.$message({message: response.data.msg,type: 'error'});
                        }
                        this.loading = false;
                    },response => {
                        this.loading = false;
                    });
                },
                chooseGoods(item) {
                    // 兑换券
                    if(this.form.use_type == 8) {
                        if(this.goods_id&&this.goods_id.length>=1) {
                            this.$message.error("兑换券不能添加多个商品");
                            return;
                        }
                        else {
                            this.goods_id.push(item.id)
                            this.goods_name.push(item.title)
                        }
                        return;
                    }
                    // 指定商品
                    let is_exist = 0;
                    this.goods_ids.some((item1,index) => {
                        if(item1 == item.id) {
                            is_exist = 1;
                            this.$message.error("请勿重复选择");
                            return true;
                        }
                    })
                    if(is_exist == 1) {
                        return;
                    }
                    this.goods_ids.push(item.id)
                    this.goods_names.push(item.title)
                },
                closeGoods(index) {
                    console.log(index)
                    if(this.form.use_type == 2){
                        this.goods_ids.splice(index,1)
                        this.goods_names.splice(index,1)
                    }
                    else if(this.form.use_type == 8) {
                        this.goods_id = [];
                        this.goods_name = [];
                    }
                },

                searchCategory() {
                    let that = this;
                    this.loading = true;
                    this.$http.post(category_url,{keyword:this.category_keyword}).then(response => {
                        if (response.data.result) {
                            this.category_list = response.data.data;
                        } else {
                            this.$message({message: response.data.msg,type: 'error'});
                        }
                        this.loading = false;
                    },response => {
                        this.loading = false;
                    });
                },
                chooseCategory(item) {
                    let is_exist = 0
                    this.category_ids.some((item1,index) => {
                        if(item1 == item.id) {
                            is_exist = 1;
                            this.$message.error("请勿重复选择");
                            return true;
                        }
                    })
                    if(is_exist == 1) {
                        return;
                    }
                    this.category_ids.push(item.id)
                    this.category_names.push(item.name)
                    console.log(this.category_names)
                },
                closeCategory(index) {
                    console.log(index)
                    this.category_ids.splice(index,1)
                    this.category_names.splice(index,1)

                },

                // 门店
                searchStore() {
                    let that = this;
                    this.loading = true;
                    this.$http.post(store_url,{keyword:this.store_keyword}).then(response => {
                        if (response.data.result) {
                            this.store_list = response.data.data;
                        } else {
                            this.$message({message: response.data.msg,type: 'error'});
                        }
                        this.loading = false;
                    },response => {
                        this.loading = false;
                    });
                },
                chooseStore(item) {
                    let is_exist = 0
                    this.store_ids.some((item1,index) => {
                        if(item1 == item.id) {
                            is_exist = 1;
                            this.$message.error("请勿重复选择");
                            return true;
                        }
                    })
                    if(is_exist == 1) {
                        return;
                    }
                    this.store_ids.push(item.id)
                    this.store_names.push(item.store_name)
                    console.log(this.store_names)
                },
                closeStore(index) {
                    console.log(index)
                    this.store_ids.splice(index,1)
                    this.store_names.splice(index,1)
                },
                // 门店

                // 酒店
                searchHotel() {
                    let that = this;
                    this.loading = true;
                    this.$http.post(hotel_url,{keyword:this.hotel_keyword}).then(response => {
                        if (response.data.result) {
                            this.hotel_list = response.data.data;
                        } else {
                            this.$message({message: response.data.msg,type: 'error'});
                        }
                        this.loading = false;
                    },response => {
                        this.loading = false;
                    });
                },
                chooseHotel(item) {
                    let is_exist = 0
                    this.hotel_ids.some((item1,index) => {
                        if(item1 == item.id) {
                            is_exist = 1;
                            this.$message.error("请勿重复选择");
                            return true;
                        }
                    })
                    if(is_exist == 1) {
                        return;
                    }
                    this.hotel_ids.push(item.id)
                    this.hotel_names.push(item.hotel_name)
                },
                closeHotel(index) {
                    console.log(index)
                    this.hotel_ids.splice(index,1)
                    this.hotel_names.splice(index,1)
                },
                // 酒店
                submitForm(formName) {
                    let that = this;
                    if(this.form.coupon_method == 1) {
                        if(!this.form.deduct) {
                            this.$message.error("立减不能为空")
                            return;
                        }
                        if(!this.form.discount) {
                            this.$message.error("折扣不能为空")
                            return;
                        }
                    }
                    let json = {
                        coupon:{
                            display_order:this.form.display_order,
                            name:this.form.name,
                            status:this.form.status || '0',
                            use_type:this.form.use_type || '0',
                            enough:this.form.enough,
                            level_limit:this.form.level_limit,
                            time_limit:this.form.time_limit || '0',
                            time_days:this.form.time_days,
                            is_complex:this.form.is_complex,
                            coupon_method:this.form.coupon_method,
                            get_type:this.form.get_type,
                            get_max:this.form.get_max,
                            total:this.form.total,
                            deduct:this.form.deduct || '0',
                            discount:this.form.discount || '0',
                        },
                        category_ids:this.category_ids,
                        category_names:this.category_names,
                        goods_ids:this.goods_ids,
                        goods_names:this.goods_names,
                        store_ids:this.store_ids,
                        store_names:this.store_names,
                        goods_id:this.goods_id,
                        goods_name:this.goods_name,
                        hotel_ids:this.hotel_ids,
                        hotel_names:this.hotel_names,
                        time:{start:"",end:""}
                    };
                    
                    if(this.form.time && this.form.time.length>0) {
                        json.time.start = this.form.time[0]/1000;
                        json.time.end = this.form.time[1]/1000;
                    }
                    if(this.id) {
                        json.id = this.id
                    }
                    this.$refs[formName].validate((valid) => {
                        if (valid) {
                            let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                            this.$http.post(this.submit_url,json).then(response => {
                                if (response.data.result) {
                                    this.$message({type: 'success',message: '操作成功!'});
                                    this.goBack();
                                } else {
                                    this.$message({message: response.data.msg,type: 'error'});
                                }
                                loading.close();
                            },response => {
                                loading.close();
                            });
                        }
                        else {
                            console.log('error submit!!');
                            return false;
                        }
                    });
                },
                
                goBack() {
                    history.go(-1)
                },
                goParent() {
                    window.location.href = `{!! yzWebFullUrl('coupon.coupon.index') !!}`;
                },
                openUpload(str) {
                    this.chooseImgName = str;
                    this.uploadShow = true;
                },
                changeProp(val) {
                    if(val == true) {
                        this.uploadShow = false;
                    }
                    else {
                        this.uploadShow = true;
                    }
                },
                sureImg(name,image,image_url) {
                    console.log(name)
                    console.log(image)
                    console.log(image_url)
                    this.form[name] = image;
                    this.form[name+'_url'] = image_url;
                },
                clearImg(str) {
                    this.form[str] = "";
                    this.form[str+'_url'] = "";
                    this.$forceUpdate();
                },
                // 字符转义
                escapeHTML(a) {
                    a = "" + a;
                    return a.replace(/&amp;/g, "&").replace(/&lt;/g, "<").replace(/&gt;/g, ">").replace(/&quot;/g, "\"").replace(/&apos;/g, "'");;
                },
            },
        })

    </script>
@endsection


